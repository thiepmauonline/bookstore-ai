<?php

namespace Tests\Feature;

use App\Exceptions\OrderException;
use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Mechanisms\HandleRequests\HandleRequests;
use Tests\TestCase;

class CustomerAccessTest extends TestCase
{
    use RefreshDatabase;

    private const SHIPPING = [
        'receiver_name' => 'Nguyễn Văn A', 'phone' => '0987654321',
        'province' => 'Hà Nội', 'ward' => 'Dịch Vọng', 'address' => '144 Xuân Thủy',
    ];

    public function test_locked_customer_is_logged_out_without_losing_admin_session_or_cart(): void
    {
        $customer = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($admin, 'admin')->actingAs($customer, 'web');
        $this->withSession(['cart' => ['sentinel']]);
        User::whereKey($customer->id)->update(['status' => 'banned']);

        $this->get('/')->assertRedirect(route('login'))->assertSessionHas('message');
        $this->assertGuest('web');
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertSame(['sentinel'], session('cart'));
        $this->get('/admin')->assertOk();
    }

    public function test_open_checkout_cannot_create_order_after_account_is_locked(): void
    {
        $customer = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $book = Book::create(['title' => 'Giáo trình Web', 'price' => 100000, 'quantity' => 5]);
        $this->actingAs($customer, 'web');
        app(CartService::class)->add($book->id);
        $response = $this->get('/checkout')->assertOk();
        preg_match('/wire:snapshot="([^"]+)"/', $response->getContent(), $matches);
        User::whereKey($customer->id)->update(['status' => 'banned']);

        $this->postJson(app(HandleRequests::class)->getUpdateUri(), [
            'components' => [[
                'snapshot' => html_entity_decode($matches[1], ENT_QUOTES),
                'updates' => self::SHIPPING,
                'calls' => [['path' => '', 'method' => 'placeOrder', 'params' => []]],
            ]],
        ], ['X-Livewire' => 'true'])->assertRedirect(route('login'));

        $this->assertSame(0, Order::count());
        $this->assertSame(5, $book->fresh()->quantity);
        $this->assertSame(1, app(CartService::class)->getCount());
    }

    public function test_order_service_rechecks_database_status_before_changing_stock(): void
    {
        $customer = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $book = Book::create(['title' => 'Giáo trình Web', 'price' => 100000, 'quantity' => 5]);
        User::whereKey($customer->id)->update(['status' => 'banned']);

        try {
            app(OrderService::class)->place($customer, [$book->id => ['quantity' => 1]], self::SHIPPING);
            $this->fail('Tài khoản đã khóa không được đặt hàng dù model trong bộ nhớ vẫn active.');
        } catch (OrderException $e) {
            $this->assertStringContainsString('bị khóa', $e->getMessage());
        }
        $this->assertSame(0, Order::count());
        $this->assertSame(5, $book->fresh()->quantity);
        $this->assertDatabaseCount('addresses', 0);
    }

    public function test_checkout_requires_login_even_after_page_has_already_opened(): void
    {
        $customer = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $book = Book::create(['title' => 'Giáo trình Web', 'price' => 100000, 'quantity' => 5]);
        $this->actingAs($customer, 'web');
        app(CartService::class)->add($book->id);
        $response = $this->get('/checkout')->assertOk();
        preg_match('/wire:snapshot="([^"]+)"/', $response->getContent(), $matches);
        auth('web')->logout();

        $this->postJson(app(HandleRequests::class)->getUpdateUri(), [
            'components' => [[
                'snapshot' => html_entity_decode($matches[1], ENT_QUOTES),
                'updates' => self::SHIPPING,
                'calls' => [['path' => '', 'method' => 'placeOrder', 'params' => []]],
            ]],
        ], ['X-Livewire' => 'true'])->assertUnauthorized();
        $this->assertSame(0, Order::count());
    }
}
