<?php

namespace App\Livewire\Admin;

use App\Enums\OrderStatus;
use App\Exceptions\OrderException;
use App\Models\Order;
use App\Services\OrderService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class OrderManager extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    #[Url]
    public $search = '';

    #[Url]
    public $statusFilter = '';

    public $viewingOrder = null;
    public $cancelReason = '';

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
        $this->loadOrder($orderId);
        $this->cancelReason = '';
        $this->dispatch('show-modal');
    }

    public function updateStatus($orderId, string $newStatus)
    {
        $next = OrderStatus::tryFrom($newStatus);
        if (! $next) {
            session()->flash('error', 'Trạng thái không hợp lệ.');
            return;
        }

        if ($next === OrderStatus::Cancelled) {
            $this->validate(['cancelReason' => 'required|string|max:250'], [
                'cancelReason.required' => 'Vui lòng nhập lý do hủy đơn.',
            ]);
        }

        try {
            $order = app(OrderService::class)->changeStatus(Order::findOrFail($orderId), $next, $this->cancelReason);
            session()->flash('message', "Đơn {$order->order_code}: đã chuyển sang \"{$next->label()}\".");
        } catch (OrderException $e) {
            session()->flash('error', $e->getMessage());
        }

        $this->cancelReason = '';
        $this->loadOrder($orderId);
    }

    private function loadOrder($orderId): void
    {
        $this->viewingOrder = Order::with(['items.book', 'address', 'coupon', 'user'])->findOrFail($orderId);
    }

    public function render()
    {
        $orders = Order::with('user')
            ->when($this->search, function ($query) {
                // Gom điều kiện OR vào một nhóm để không phá điều kiện lọc trạng thái.
                $query->where(function ($q) {
                    $q->where('order_code', 'like', '%'.$this->search.'%')
                        ->orWhereHas('user', fn ($u) => $u
                            ->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('phone', 'like', '%'.$this->search.'%'));
                });
            })
            ->when(OrderStatus::tryFrom((string) $this->statusFilter), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(10);

        return view('livewire.admin.order-manager', [
            'orders' => $orders,
            'statuses' => OrderStatus::cases(),
        ]);
    }
}
