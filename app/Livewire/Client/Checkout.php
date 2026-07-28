<?php

namespace App\Livewire\Client;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Services\CartService;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Coupon;
use App\Models\Book;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.client')]
class Checkout extends Component
{
    public $cartItems = [];
    public $subtotal = 0;
    public $total = 0;

    // Coupon
    public $couponCode = '';
    public $appliedCoupon = null;
    public $discountAmount = 0;

    // Address Fields
    public $receiver_name = '';
    public $phone = '';
    public $province = '';
    public $district = '';
    public $ward = '';
    public $address = '';
    public $note = '';
    
    public $payment_method = 'cod'; // default COD

    protected $rules = [
        'receiver_name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'province' => 'required|string',
        'district' => 'required|string',
        'ward' => 'required|string',
        'address' => 'required|string|max:500',
        'payment_method' => 'required|in:cod,vnpay,momo',
    ];

    public function mount(CartService $cartService)
    {
        // Redirect if cart is empty
        if ($cartService->getCount() == 0) {
            return redirect()->route('cart')->with('error', 'Giỏ hàng của bạn đang trống!');
        }

        // Must be logged in
        if (!auth()->check()) {
            session()->put('url.intended', route('checkout'));
            return redirect()->route('login')->with('message', 'Vui lòng đăng nhập để tiếp tục thanh toán.');
        }

        $this->loadCart($cartService);

        // Pre-fill default address if available
        $defaultAddress = Address::where('user_id', auth()->id())->where('is_default', true)->first();
        if (!$defaultAddress) {
            $defaultAddress = Address::where('user_id', auth()->id())->first();
        }

        if ($defaultAddress) {
            $this->receiver_name = $defaultAddress->receiver_name;
            $this->phone = $defaultAddress->phone;
            $this->province = $defaultAddress->province;
            $this->district = $defaultAddress->district;
            $this->ward = $defaultAddress->ward;
            $this->address = $defaultAddress->address;
        } else {
            // Fill from user profile
            $this->receiver_name = auth()->user()->name;
            $this->phone = auth()->user()->phone;
        }
    }

    public function loadCart(CartService $cartService)
    {
        $this->cartItems = $cartService->getCart();
        $this->subtotal = $cartService->getTotal();
        $this->calculateTotal();
    }

    public function applyCoupon()
    {
        $this->resetErrorBag('couponCode');
        
        if (empty($this->couponCode)) {
            $this->addError('couponCode', 'Vui lòng nhập mã giảm giá.');
            return;
        }

        $coupon = Coupon::where('code', strtoupper($this->couponCode))->first();

        if (!$coupon) {
            $this->addError('couponCode', 'Mã giảm giá không tồn tại.');
            return;
        }

        if (!now()->between($coupon->start_date, $coupon->end_date)) {
            $this->addError('couponCode', 'Mã giảm giá đã hết hạn hoặc chưa có hiệu lực.');
            return;
        }

        if ($coupon->quantity <= 0) {
            $this->addError('couponCode', 'Mã giảm giá đã hết lượt sử dụng.');
            return;
        }

        $this->appliedCoupon = $coupon;
        $this->calculateTotal();
        session()->flash('coupon_message', 'Áp dụng mã giảm giá thành công!');
    }

    public function removeCoupon()
    {
        $this->appliedCoupon = null;
        $this->couponCode = '';
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->discountAmount = 0;
        if ($this->appliedCoupon) {
            $this->discountAmount = $this->appliedCoupon->discount;
        }
        
        $this->total = $this->subtotal - $this->discountAmount;
        if ($this->total < 0) {
            $this->total = 0;
        }
    }

    public function placeOrder(CartService $cartService)
    {
        $this->validate();

        // 1. Kiểm tra lại giỏ hàng và tồn kho
        $cart = $cartService->getCart();
        if (empty($cart)) {
            return redirect()->route('cart')->with('error', 'Giỏ hàng trống!');
        }

        foreach ($cart as $id => $item) {
            $book = Book::find($id);
            if (!$book || $book->quantity < $item['quantity']) {
                session()->flash('checkout_error', 'Sách "' . $item['title'] . '" không đủ số lượng tồn kho (Còn ' . ($book ? $book->quantity : 0) . '). Vui lòng cập nhật lại giỏ hàng.');
                return;
            }
        }

        DB::beginTransaction();

        try {
            // 2. Lưu địa chỉ
            $address = Address::firstOrCreate(
                [
                    'user_id' => auth()->id(),
                    'receiver_name' => $this->receiver_name,
                    'phone' => $this->phone,
                    'province' => $this->province,
                    'district' => $this->district,
                    'ward' => $this->ward,
                    'address' => $this->address,
                ],
                ['is_default' => !Address::where('user_id', auth()->id())->exists()] // Set default if it's the first
            );

            // 3. Tạo Order
            $order = Order::create([
                'user_id' => auth()->id(),
                'address_id' => $address->id,
                'coupon_id' => $this->appliedCoupon ? $this->appliedCoupon->id : null,
                'order_code' => 'ORD-' . strtoupper(Str::random(8)),
                'total_price' => $this->total,
                'payment_method' => $this->payment_method,
                'payment_status' => 'pending',
                'status' => 'pending',
                'note' => $this->note,
            ]);

            // 4. Tạo Order Items & Trừ tồn kho
            foreach ($cart as $id => $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'book_id' => $id,
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                ]);

                // Trừ tồn kho
                Book::where('id', $id)->decrement('quantity', $item['quantity']);
            }

            // 5. Trừ lượt Coupon
            if ($this->appliedCoupon) {
                Coupon::where('id', $this->appliedCoupon->id)->decrement('quantity', 1);
            }

            DB::commit();

            // Clear cart
            $cartService->clear();
            $this->dispatch('cart-updated');

            // Redirect to success or payment gateway
            return redirect()->route('home')->with('message', 'Đặt hàng thành công! Mã đơn hàng của bạn là ' . $order->order_code);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('checkout_error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.client.checkout');
    }
}
