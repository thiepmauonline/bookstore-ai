<?php

namespace App\Livewire\Client;

use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class CartIndicator extends Component
{
    #[On('cart-updated')]
    public function refreshCount(): void {}

    public function render(CartService $cart)
    {
        return view('livewire.client.cart-indicator', ['count' => $cart->getCount()]);
    }
}
