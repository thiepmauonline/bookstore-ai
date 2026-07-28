<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Order;

#[Layout('components.layouts.admin')]
class OrderManager extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $statusFilter = '';

    public $viewingOrder = null;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function viewDetails($orderId)
    {
        $this->viewingOrder = Order::with(['items.book', 'address', 'coupon', 'user'])->findOrFail($orderId);
        $this->dispatch('show-modal');
    }

    public function updateStatus($orderId, $newStatus)
    {
        $order = Order::findOrFail($orderId);
        $order->update(['status' => $newStatus]);
        
        // Cập nhật payment_status nếu hoàn thành
        if ($newStatus === 'completed') {
            $order->update(['payment_status' => 'paid']);
        }
        
        if ($this->viewingOrder && $this->viewingOrder->id === $orderId) {
            $this->viewingOrder->refresh();
        }
        
        session()->flash('message', 'Đã cập nhật trạng thái đơn hàng ' . $order->order_code);
    }

    public function render()
    {
        $query = Order::with('user');

        if ($this->search) {
            $query->where('order_code', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%');
                  });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $orders = $query->latest()->paginate(10);

        return view('livewire.admin.order-manager', [
            'orders' => $orders
        ]);
    }
}
