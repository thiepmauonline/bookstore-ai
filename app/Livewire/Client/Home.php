<?php

namespace App\Livewire\Client;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Book;
use App\Models\Major;
use App\Models\Course;

#[Layout('components.layouts.client')]
class Home extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $selectedMajor = '';
    public $selectedCourse = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedMajor()
    {
        $this->selectedCourse = '';
        $this->resetPage();
    }

    public function updatedSelectedCourse()
    {
        $this->resetPage();
    }

    public function addToCart($bookId, \App\Services\CartService $cartService)
    {
        $cartService->add($bookId, 1);
        
        // Cập nhật sự kiện để navbar update số lượng
        $this->dispatch('cart-updated');
        session()->flash('message', 'Đã thêm sách vào giỏ hàng!');
    }

    public function render()
    {
        $majors = Major::all();
        $courses = collect();

        if ($this->selectedMajor) {
            $courses = Course::where('major_id', $this->selectedMajor)->get();
        }

        $books = Book::with(['author', 'category'])
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%');
            })
            ->when($this->selectedCourse, function ($query) {
                $query->where('course_id', $this->selectedCourse);
            })
            ->when($this->selectedMajor && !$this->selectedCourse, function ($query) {
                // Find books belonging to any course in this major
                $query->whereHas('course', function ($q) {
                    $q->where('major_id', $this->selectedMajor);
                });
            })
            ->latest()
            ->paginate(8);

        return view('livewire.client.home', [
            'books' => $books,
            'majors' => $majors,
            'courses' => $courses,
        ]);
    }
}
