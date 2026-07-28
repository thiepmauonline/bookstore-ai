<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Author;
use Illuminate\Support\Str;

#[Layout('components.layouts.admin')]
class AuthorManager extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $model_id;
    public $name;
    public $biography;
    
    public $isEditMode = false;

    protected $rules = array (
  'name' => 'required|string|max:255',
  'biography' => 'nullable|string',
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
        $this->reset(['model_id', 'name', 'biography']);
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
        \App\Models\Author::create([
            'name' => $this->name,
            'biography' => $this->biography,
            
        ]);
        $this->dispatch('hide-modal');
        session()->flash('message', 'Thêm mới thành công!');
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->isEditMode = true;
        
        $item = \App\Models\Author::findOrFail($id);
        $this->model_id = $item->id;
        $this->name = $item->name;
        $this->biography = $item->biography;
        
        $this->dispatch('show-modal');
    }

    public function update()
    {
        $this->validate();
        $item = \App\Models\Author::findOrFail($this->model_id);
        $item->update([
            'name' => $this->name,
            'biography' => $this->biography,
            
        ]);
        $this->dispatch('hide-modal');
        session()->flash('message', 'Cập nhật thành công!');
    }

    public function delete($id)
    {
        // For restrict relationships, this might fail if books exist. Catch it?
        try {
            \App\Models\Author::findOrFail($id)->delete();
            session()->flash('message', 'Đã xóa thành công!');
        } catch (\Exception $e) {
            session()->flash('error', 'Không thể xóa vì đang có Sách tham chiếu đến dữ liệu này!');
        }
    }

    public function render()
    {
        $items = \App\Models\Author::where('name', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.' . strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', 'AuthorManager')), [
            'items' => $items
        ]);
    }
}