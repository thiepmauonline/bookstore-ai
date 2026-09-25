<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Category;
use Illuminate\Support\Str;

#[Layout('components.layouts.admin')]
class CategoryManager extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $model_id;
    public $name;
    public $slug;
    
    public $isEditMode = false;

    protected $rules = array (
  'name' => 'required|string|max:255',
  'slug' => 'required|string|max:255',
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
        $this->reset(['model_id', 'name', 'slug']);
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
        \App\Models\Category::create([
            'name' => $this->name,
            'slug' => $this->slug,
            
        ]);
        $this->dispatch('hide-modal');
        session()->flash('message', 'Thêm mới thành công!');
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->isEditMode = true;
        
        $item = \App\Models\Category::findOrFail($id);
        $this->model_id = $item->id;
        $this->name = $item->name;
        $this->slug = $item->slug;
        
        $this->dispatch('show-modal');
    }

    public function update()
    {
        $this->validate();
        $item = \App\Models\Category::findOrFail($this->model_id);
        $item->update([
            'name' => $this->name,
            'slug' => $this->slug,
            
        ]);
        $this->dispatch('hide-modal');
        session()->flash('message', 'Cập nhật thành công!');
    }

    public function delete($id)
    {
        $item = \App\Models\Category::withCount('books')->findOrFail($id);

        // CSDL không dùng khóa ngoại nên phải tự kiểm tra dữ liệu đang tham chiếu trước khi xóa.
        if ($item->books_count > 0) {
            session()->flash('error', "Không thể xóa vì đang có {$item->books_count} sách thuộc mục này. Hãy chuyển các sách sang mục khác trước.");
            return;
        }

        $item->delete();
        session()->flash('message', 'Đã xóa thành công!');
    }

    public function render()
    {
        $items = \App\Models\Category::where('name', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.' . strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', 'CategoryManager')), [
            'items' => $items
        ]);
    }
}