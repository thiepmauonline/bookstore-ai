<?php

namespace App\Livewire\Client;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Exceptions\OrderException;
use App\Services\CartService;
use App\Services\OrderService;
use App\Models\Address;
use App\Models\Coupon;

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

    protected $rules = [
        'receiver_name' => 'required|string|max:255',
        'phone' => ['required', 'regex:/^0\d{9,10}$/'],
        'province' => 'required|string',
        'district' => 'required|string',
        'ward' => 'required|string',
        'address' => 'required|string|max:500',
        'note' => 'nullable|string|max:1000',
    ];

    protected $messages = [
        'phone.regex' => 'Số điện thoại phải bắt đầu bằng 0 và có 10-11 chữ số.',
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

        if ($reason = $coupon->unavailableReason()) {
            $this->addError('couponCode', $reason);
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
            $this->discountAmount = min((float) $this->appliedCoupon->discount, (float) $this->subtotal);
        }
        
        $this->total = $this->subtotal - $this->discountAmount;
        if ($this->total < 0) {
            $this->total = 0;
        }
    }

    public function placeOrder(CartService $cartService, OrderService $orderService)
    {
        $this->validate();

        try {
            $order = $orderService->place(
                auth()->user(),
                $cartService->getCart(),
                $this->only(['receiver_name', 'phone', 'province', 'district', 'ward', 'address']),
                $this->appliedCoupon?->code,
                $this->note,
            );
        } catch (OrderException $e) {
            session()->flash('checkout_error', $e->getMessage());
            return;
        }

        $cartService->clear();
        $this->dispatch('cart-updated');

        return redirect()->route('my.orders')->with('message', 'Đặt hàng thành công! Mã đơn hàng của bạn là '.$order->order_code);
    }

    public function render()
    {
        return view('livewire.client.checkout');
    }
}
