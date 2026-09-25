<?php

namespace App\Livewire\Client;

use App\Exceptions\OrderException;
use App\Models\Order;
use App\Services\OrderService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.client')]
class MyOrders extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $selectedOrder = null;
    public $cancelReason = '';

    public function viewOrder($id)
    {
        $this->selectedOrder = $this->findOwnOrder($id);
        $this->cancelReason = '';
        $this->dispatch('show-order-modal');
    }

    public function cancelOrder($id)
    {
        $this->validate(['cancelReason' => 'nullable|string|max:250']);

        try {
            $order = app(OrderService::class)->cancelByCustomer($this->findOwnOrder($id), auth()->user(), $this->cancelReason);
            session()->flash('order_message', "Đã hủy đơn hàng {$order->order_code}.");
        } catch (OrderException $e) {
            session()->flash('order_error', $e->getMessage());
        }

        $this->selectedOrder = $this->findOwnOrder($id);
        $this->cancelReason = '';
    }

    private function findOwnOrder($id): Order
    {
        return Order::with(['items.book', 'address', 'coupon'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
    }

    public function render()
    {
        $orders = Order::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('livewire.client.my-orders', [
            'orders' => $orders,
        ]);
    }
}
