<?php

namespace App\Livewire\Client;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Order;

#[Layout('components.layouts.client')]
class MyOrders extends Component
{
    public $selectedOrder = null;

    public function viewOrder($id)
    {
        $this->selectedOrder = Order::with('items.book')->where('user_id', auth()->id())->findOrFail($id);
        $this->dispatch('show-order-modal');
    }

    public function render()
    {
        $orders = Order::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.client.my-orders', [
            'orders' => $orders
        ]);
    }
}
