<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\OrderException;
use App\Livewire\Admin\OrderManager;
use App\Livewire\Client\Checkout;
use App\Livewire\Client\MyOrders;
use App\Models\Book;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private const SHIPPING = [
        'receiver_name' => 'Nguyễn Văn A',
        'phone' => '0987654321',
        'province' => 'Hà Nội',
        'district' => 'Cầu Giấy',
        'ward' => 'Dịch Vọng',
        'address' => '144 Xuân Thủy',
    ];

    private function customer(): User
    {
        return User::factory()->create(['role' => 'user', 'status' => 'active']);
    }

    private function book(int $quantity = 10, int $price = 100000): Book
    {
        return Book::create(['title' => 'Sách '.uniqid(), 'price' => $price, 'quantity' => $quantity]);
    }

    private function coupon(array $attributes = []): Coupon
    {
        return Coupon::create($attributes + [
            'code' => 'GIAM20K',
            'discount' => 20000,
            'quantity' => 5,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
    }

    private function placeOrder(User $user, Book $book, int $quantity = 2, ?string $coupon = null): Order
    {
        return app(OrderService::class)->place($user, [$book->id => ['quantity' => $quantity]], self::SHIPPING, $coupon);
    }

    public function test_placing_order_uses_database_price_and_updates_stock_and_coupon(): void
    {
        $user = $this->customer();
        $book = $this->book(quantity: 10, price: 150000);
        $coupon = $this->coupon();

        // Giá trong giỏ (session) bị sửa thành 1đ nhưng hệ thống vẫn tính theo giá CSDL.
        $order = app(OrderService::class)->place(
            $user,
            [$book->id => ['quantity' => 2, 'price' => 1]],
            self::SHIPPING,
            'giam20k'
        );

        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(PaymentStatus::Unpaid, $order->payment_status);
        $this->assertEquals(300000, $order->subtotal);
        $this->assertEquals(20000, $order->discount_amount);
        $this->assertEquals(280000, $order->total_price);
        $this->assertSame(8, $book->fresh()->quantity);
        $this->assertSame(4, $coupon->fresh()->quantity);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'book_id' => $book->id, 'price' => 150000, 'quantity' => 2]);
    }

    public function test_order_is_rejected_without_side_effects_when_stock_is_insufficient(): void
    {
        $user = $this->customer();
        $book = $this->book(quantity: 1);

        $this->expectException(OrderException::class);

        try {
            $this->placeOrder($user, $book, quantity: 2);
        } finally {
            $this->assertSame(1, $book->fresh()->quantity);
            $this->assertSame(0, Order::count());
        }
    }

    public function test_expired_or_used_up_coupon_is_rejected(): void
    {
        $user = $this->customer();
        $book = $this->book();
        $this->coupon(['code' => 'HETLUOT', 'quantity' => 0]);
        $this->coupon(['code' => 'HETHAN', 'end_date' => now()->subHour()]);

        foreach (['HETLUOT' => 'hết lượt', 'HETHAN' => 'hết hạn'] as $code => $reason) {
            try {
                $this->placeOrder($user, $book, coupon: $code);
                $this->fail("Mã {$code} lẽ ra phải bị từ chối.");
            } catch (OrderException $e) {
                $this->assertStringContainsString($reason, $e->getMessage());
            }
        }
        $this->assertSame(10, $book->fresh()->quantity);
    }

    public function test_status_follows_workflow_and_completion_marks_order_paid(): void
    {
        $order = $this->placeOrder($this->customer(), $this->book());
        $service = app(OrderService::class);

        try {
            $service->changeStatus($order, OrderStatus::Completed);
            $this->fail('Không được nhảy thẳng từ Chờ xác nhận sang Hoàn thành.');
        } catch (OrderException) {
        }

        $service->changeStatus($order, OrderStatus::Confirmed);
        $service->changeStatus($order, OrderStatus::Shipping);
        $order = $service->changeStatus($order, OrderStatus::Completed);

        $this->assertSame(OrderStatus::Completed, $order->status);
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);

        $this->expectException(OrderException::class);
        $service->changeStatus($order, OrderStatus::Cancelled, 'Thử hủy đơn đã hoàn thành');
    }

    public function test_cancelling_restores_stock_and_coupon_usage(): void
    {
        $book = $this->book(quantity: 10);
        $coupon = $this->coupon();
        $order = $this->placeOrder($this->customer(), $book, quantity: 3, coupon: 'GIAM20K');
        $this->assertSame(7, $book->fresh()->quantity);

        $order = app(OrderService::class)->changeStatus($order, OrderStatus::Cancelled, 'Hết hàng tại kho');

        $this->assertSame(OrderStatus::Cancelled, $order->status);
        $this->assertSame('Hết hàng tại kho', $order->cancel_reason);
        $this->assertSame(10, $book->fresh()->quantity);
        $this->assertSame(5, $coupon->fresh()->quantity);
    }

    public function test_customer_can_cancel_only_own_pending_order(): void
    {
        $owner = $this->customer();
        $book = $this->book(quantity: 5);
        $order = $this->placeOrder($owner, $book, quantity: 2);

        $this->actingAs($this->customer());
        try {
            Livewire::test(MyOrders::class)->call('cancelOrder', $order->id);
            $this->fail('Khách khác không được hủy đơn này.');
        } catch (ModelNotFoundException) {
        }
        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);

        $this->actingAs($owner);
        Livewire::test(MyOrders::class)
            ->set('cancelReason', 'Đặt nhầm')
            ->call('cancelOrder', $order->id)
            ->assertSee('Đã hủy đơn hàng');
        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(5, $book->fresh()->quantity);
    }

    public function test_customer_cannot_cancel_after_store_confirms(): void
    {
        $owner = $this->customer();
        $order = $this->placeOrder($owner, $this->book());
        app(OrderService::class)->changeStatus($order, OrderStatus::Confirmed);

        $this->expectException(OrderException::class);
        app(OrderService::class)->cancelByCustomer($order, $owner);
    }

    public function test_checkout_page_places_cod_order_and_clears_cart(): void
    {
        $user = $this->customer();
        $book = $this->book(quantity: 4);
        $this->actingAs($user);
        app(CartService::class)->add($book->id, 2);

        Livewire::test(Checkout::class)
            ->set(self::SHIPPING)
            ->call('placeOrder')
            ->assertHasNoErrors()
            ->assertRedirect(route('my.orders'));

        $order = Order::sole();
        $this->assertSame($user->id, $order->user_id);
        $this->assertSame('cod', $order->payment_method->value);
        $this->assertSame(2, $book->fresh()->quantity);
        $this->assertSame(0, app(CartService::class)->getCount());
    }

    public function test_checkout_validates_phone_number(): void
    {
        $this->actingAs($this->customer());
        app(CartService::class)->add($this->book()->id);

        Livewire::test(Checkout::class)
            ->set(self::SHIPPING)
            ->set('phone', '12345')
            ->call('placeOrder')
            ->assertHasErrors('phone');

        $this->assertSame(0, Order::count());
    }

    public function test_admin_must_give_reason_when_cancelling(): void
    {
        $order = $this->placeOrder($this->customer(), $this->book());
        $this->actingAs(User::factory()->create(['role' => 'admin', 'status' => 'active']), 'admin');

        Livewire::test(OrderManager::class)
            ->call('updateStatus', $order->id, 'cancelled')
            ->assertHasErrors('cancelReason');
        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);

        Livewire::test(OrderManager::class)
            ->call('updateStatus', $order->id, 'not-a-status')
            ->assertSee('Trạng thái không hợp lệ');

        Livewire::test(OrderManager::class)
            ->set('cancelReason', 'Khách không nghe máy')
            ->call('updateStatus', $order->id, 'cancelled')
            ->assertHasNoErrors();
        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
    }
}
