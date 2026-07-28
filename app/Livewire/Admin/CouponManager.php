<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Coupon;

#[Layout('components.layouts.admin')]
class CouponManager extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    
    public $coupon_id;
    public $code;
    public $discount;
    public $quantity;
    public $start_date;
    public $end_date;

    public $isEditMode = false;

    protected $rules = [
        'code' => 'required|string|max:50|unique:coupons,code',
        'discount' => 'required|numeric|min:0',
        'quantity' => 'required|integer|min:1',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->reset(['coupon_id', 'code', 'discount', 'quantity', 'start_date', 'end_date']);
        $this->isEditMode = false;
        $this->resetErrorBag();
    }

    public function create()
    {
        $this->resetForm();
        $this->dispatch('show-modal');
    }

    public function store()
    {
        $this->validate();
        
        Coupon::create([
            'code' => strtoupper($this->code),
            'discount' => $this->discount,
            'quantity' => $this->quantity,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ]);

        $this->dispatch('hide-modal');
        session()->flash('message', 'Thêm mã giảm giá thành công!');
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->isEditMode = true;
        
        $item = Coupon::findOrFail($id);
        $this->coupon_id = $item->id;
        $this->code = $item->code;
        $this->discount = $item->discount;
        $this->quantity = $item->quantity;
        $this->start_date = $item->start_date->format('Y-m-d\TH:i');
        $this->end_date = $item->end_date->format('Y-m-d\TH:i');
        
        $this->dispatch('show-modal');
    }

    public function update()
    {
        $this->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $this->coupon_id,
            'discount' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $item = Coupon::findOrFail($this->coupon_id);
        $item->update([
            'code' => strtoupper($this->code),
            'discount' => $this->discount,
            'quantity' => $this->quantity,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
        ]);

        $this->dispatch('hide-modal');
        session()->flash('message', 'Cập nhật mã giảm giá thành công!');
    }

    public function delete($id)
    {
        try {
            Coupon::findOrFail($id)->delete();
            session()->flash('message', 'Đã xóa mã giảm giá!');
        } catch (\Exception $e) {
            session()->flash('error', 'Không thể xóa vì mã đang được sử dụng trong đơn hàng!');
        }
    }

    public function render()
    {
        $coupons = Coupon::where('code', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.coupon-manager', [
            'coupons' => $coupons
        ]);
    }
}
