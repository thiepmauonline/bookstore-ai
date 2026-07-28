<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Publisher;
use Illuminate\Support\Str;

#[Layout('components.layouts.admin')]
class PublisherManager extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $model_id;
    public $name;
    public $address;
    
    public $isEditMode = false;

    protected $rules = array (
  'name' => 'required|string|max:255',
  'address' => 'nullable|string',
);

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedName()
    {
        // Auto generate slug if slug field exists
        if (property_exists($this, 'slug') && !$this->isEditMode) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function resetForm()
    {
        $this->reset(['model_id', 'name', 'address']);
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
        \App\Models\Publisher::create([
            'name' => $this->name,
            'address' => $this->address,
            
        ]);
        $this->dispatch('hide-modal');
        session()->flash('message', 'Thêm mới thành công!');
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->isEditMode = true;
        
        $item = \App\Models\Publisher::findOrFail($id);
        $this->model_id = $item->id;
        $this->name = $item->name;
        $this->address = $item->address;
        
        $this->dispatch('show-modal');
    }

    public function update()
    {
        $this->validate();
        $item = \App\Models\Publisher::findOrFail($this->model_id);
        $item->update([
            'name' => $this->name,
            'address' => $this->address,
            
        ]);
        $this->dispatch('hide-modal');
        session()->flash('message', 'Cập nhật thành công!');
    }

    public function delete($id)
    {
        // For restrict relationships, this might fail if books exist. Catch it?
        try {
            \App\Models\Publisher::findOrFail($id)->delete();
            session()->flash('message', 'Đã xóa thành công!');
        } catch (\Exception $e) {
            session()->flash('error', 'Không thể xóa vì đang có Sách tham chiếu đến dữ liệu này!');
        }
    }

    public function render()
    {
        $items = \App\Models\Publisher::where('name', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.' . strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', 'PublisherManager')), [
            'items' => $items
        ]);
    }
}