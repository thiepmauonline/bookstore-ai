<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Exceptions\OrderException;
use App\Models\Address;
use App\Models\Book;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Toàn bộ nghiệp vụ thay đổi đơn hàng: đặt hàng, chuyển trạng thái, hủy đơn.
 * Mọi thao tác chạy trong transaction và khóa các dòng liên quan để tồn kho
 * và lượt dùng mã giảm giá luôn nhất quán khi nhiều người thao tác cùng lúc.
 */
class OrderService
{
    /**
     * Tạo đơn hàng từ giỏ hàng.
     *
     * @param  array<int|string, array{quantity:int}>  $cart  Giỏ hàng dạng [book_id => ['quantity' => n, ...]]
     * @param  array{receiver_name:string, phone:string, province:string, district:string, ward:string, address:string}  $shipping
     *
     * @throws OrderException
     */
    public function place(User $user, array $cart, array $shipping, ?string $couponCode = null, ?string $note = null): Order
    {
        if (empty($cart)) {
            throw new OrderException('Giỏ hàng của bạn đang trống.');
        }

        return DB::transaction(function () use ($user, $cart, $shipping, $couponCode, $note) {
            // Khóa các dòng sách để hai đơn đồng thời không cùng trừ một lượng tồn kho.
            $books = Book::whereIn('id', array_keys($cart))->lockForUpdate()->get()->keyBy('id');

            $subtotal = 0;
            foreach ($cart as $bookId => $item) {
                $book = $books->get($bookId);
                $quantity = (int) $item['quantity'];

                if (! $book) {
                    throw new OrderException('Một cuốn sách trong giỏ không còn được bán. Vui lòng cập nhật lại giỏ hàng.');
                }
                if ($quantity < 1 || $book->quantity < $quantity) {
                    throw new OrderException("Sách \"{$book->title}\" không đủ số lượng tồn kho (còn {$book->quantity}). Vui lòng cập nhật lại giỏ hàng.");
                }

                // Luôn tính theo giá hiện tại trong CSDL, không tin giá lưu trong session.
                $subtotal += $book->price * $quantity;
            }

            $coupon = null;
            $discount = 0;
            if ($couponCode) {
                $coupon = Coupon::where('code', strtoupper($couponCode))->lockForUpdate()->first();
                if (! $coupon) {
                    throw new OrderException('Mã giảm giá không tồn tại.');
                }
                if ($reason = $coupon->unavailableReason()) {
                    throw new OrderException($reason);
                }
                $discount = min((float) $coupon->discount, $subtotal);
            }

            $address = Address::firstOrCreate(
                ['user_id' => $user->id] + $shipping,
                ['is_default' => ! Address::where('user_id', $user->id)->exists()]
            );

            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $address->id,
                'coupon_id' => $coupon?->id,
                'order_code' => $this->generateOrderCode(),
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'total_price' => $subtotal - $discount,
                'payment_method' => PaymentMethod::Cod,
                'payment_status' => PaymentStatus::Unpaid,
                'status' => OrderStatus::Pending,
                'note' => $note ?: null,
            ]);

            foreach ($cart as $bookId => $item) {
                $book = $books->get($bookId);
                $order->items()->create([
                    'book_id' => $book->id,
                    'price' => $book->price,
                    'quantity' => (int) $item['quantity'],
                ]);
                $book->decrement('quantity', (int) $item['quantity']);
            }

            $coupon?->decrement('quantity');

            return $order;
        });
    }

    /**
     * Quản trị viên chuyển đơn sang trạng thái tiếp theo.
     *
     * @throws OrderException
     */
    public function changeStatus(Order $order, OrderStatus $next, ?string $reason = null): Order
    {
        if ($next === OrderStatus::Cancelled) {
            return $this->cancel($order, $reason ?: 'Cửa hàng hủy đơn');
        }

        return DB::transaction(function () use ($order, $next) {
            $order = Order::lockForUpdate()->findOrFail($order->id);
            $this->assertCanTransition($order, $next);

            $order->status = $next;
            if ($next === OrderStatus::Completed) {
                // Đơn COD hoàn thành nghĩa là khách đã trả tiền khi nhận hàng.
                $order->payment_status = PaymentStatus::Paid;
            }
            $order->save();

            return $order;
        });
    }

    /**
     * Khách hàng tự hủy đơn của mình (chỉ khi đơn còn chờ xác nhận).
     *
     * @throws OrderException
     */
    public function cancelByCustomer(Order $order, User $customer, ?string $reason = null): Order
    {
        return $this->cancel($order, $reason ?: 'Khách hàng hủy đơn', $customer);
    }

    /**
     * Hủy đơn: hoàn lại tồn kho cho từng sách và hoàn một lượt dùng mã giảm giá.
     * Khi truyền $customer, chỉ cho hủy đơn của chính khách đó và khi đơn còn chờ xác nhận.
     *
     * @throws OrderException
     */
    public function cancel(Order $order, string $reason, ?User $customer = null): Order
    {
        return DB::transaction(function () use ($order, $reason, $customer) {
            // Đọc lại sau khi khóa: có thể đơn vừa bị người khác đổi trạng thái.
            $order = Order::with('items')->lockForUpdate()->findOrFail($order->id);

            if ($customer) {
                if ($order->user_id !== $customer->id) {
                    throw new OrderException('Bạn không có quyền hủy đơn hàng này.');
                }
                if (! $order->status->isCancellableByCustomer()) {
                    throw new OrderException('Đơn hàng đã được cửa hàng xác nhận, bạn không thể tự hủy. Vui lòng liên hệ cửa hàng.');
                }
            }
            $this->assertCanTransition($order, OrderStatus::Cancelled);

            foreach ($order->items as $item) {
                Book::whereKey($item->book_id)->increment('quantity', $item->quantity);
            }
            if ($order->coupon_id) {
                Coupon::whereKey($order->coupon_id)->increment('quantity');
            }

            $order->update([
                'status' => OrderStatus::Cancelled,
                'cancel_reason' => Str::limit($reason, 250),
            ]);

            return $order;
        });
    }

    /** @throws OrderException */
    private function assertCanTransition(Order $order, OrderStatus $next): void
    {
        if (! $order->status->canTransitionTo($next)) {
            throw new OrderException(sprintf(
                'Không thể chuyển đơn %s từ "%s" sang "%s".',
                $order->order_code,
                $order->status->label(),
                $next->label()
            ));
        }
    }

    private function generateOrderCode(): string
    {
        do {
            $code = 'DH'.now()->format('ymd').strtoupper(Str::random(5));
        } while (Order::where('order_code', $code)->exists());

        return $code;
    }
}
