<?php

namespace App\Livewire\Client;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Services\CartService;

#[Layout('components.layouts.client')]
class Cart extends Component
{
    public $cartItems = [];
    public $total = 0;

    public function mount(CartService $cartService)
    {
        $this->loadCart($cartService);
    }

    public function loadCart(CartService $cartService)
    {
        $this->cartItems = $cartService->getCart();
        $this->total = $cartService->getTotal();
    }

    public function updateQuantity($bookId, $quantity, CartService $cartService)
    {
        $quantity = (int) $quantity;
        if ($quantity > 0) {
            $cartService->update($bookId, $quantity);
        } else {
            $cartService->remove($bookId);
        }
        
        $this->loadCart($cartService);
        $this->dispatch('cart-updated');
    }

    public function removeItem($bookId, CartService $cartService)
    {
        $cartService->remove($bookId);
        $this->loadCart($cartService);
        $this->dispatch('cart-updated');
        session()->flash('message', 'Đã xóa sản phẩm khỏi giỏ hàng!');
    }

    public function clearCart(CartService $cartService)
    {
        $cartService->clear();
        $this->loadCart($cartService);
        $this->dispatch('cart-updated');
        session()->flash('message', 'Đã xóa toàn bộ giỏ hàng!');
    }

    public function render()
    {
        return view('livewire.client.cart');
    }
}
