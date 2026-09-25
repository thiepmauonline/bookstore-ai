<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Book;
use App\Models\Category;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\Course;
use Illuminate\Support\Facades\Storage;

#[Layout('components.layouts.admin')]
class BookManager extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    // Form fields
    public $book_id;
    public $title;
    public $isbn;
    public $category_id;
    public $author_id;
    public $publisher_id;
    public $course_id;
    public $description;
    public $price;
    public $quantity;
    public $published_year;
    public $level;
    public $cover_image; // for file upload
    public $old_image; // to track existing image

    public $isEditMode = false;

    protected $rules = [
        'title' => 'required|string|max:255',
        'isbn' => 'nullable|string|max:50',
        'category_id' => 'required|exists:categories,id',
        'author_id' => 'required|exists:authors,id',
        'publisher_id' => 'required|exists:publishers,id',
        'course_id' => 'nullable|exists:courses,id', // sách tham khảo chung có thể không thuộc học phần nào
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'quantity' => 'required|integer|min:0',
        'published_year' => 'nullable|integer',
        'level' => 'nullable|string|max:50',
        'cover_image' => 'nullable|image|max:2048', // max 2MB
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function resetForm()
    {
        $this->reset([
            'book_id', 'title', 'isbn', 'category_id', 'author_id',
            'publisher_id', 'course_id', 'description', 'price',
            'quantity', 'published_year', 'level', 'cover_image', 'old_image'
        ]);
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
        $this->course_id = $this->course_id ?: null;
        $this->validate();

        $imagePath = null;
        if ($this->cover_image) {
            $imagePath = $this->cover_image->store('books', 'public');
        }

        Book::create([
            'title' => $this->title,
            'isbn' => $this->isbn,
            'category_id' => $this->category_id,
            'author_id' => $this->author_id,
            'publisher_id' => $this->publisher_id,
            'course_id' => $this->course_id,
            'description' => $this->description,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'published_year' => $this->published_year,
            'level' => $this->level,
            'cover_image' => $imagePath,
        ]);

        $this->dispatch('hide-modal');
        session()->flash('message', 'Thêm sách thành công!');
    }

    public function edit($id)
    {
        $this->resetForm();
        $this->isEditMode = true;
        
        $book = Book::findOrFail($id);
        $this->book_id = $book->id;
        $this->title = $book->title;
        $this->isbn = $book->isbn;
        $this->category_id = $book->category_id;
        $this->author_id = $book->author_id;
        $this->publisher_id = $book->publisher_id;
        $this->course_id = $book->course_id;
        $this->description = $book->description;
        $this->price = $book->price;
        $this->quantity = $book->quantity;
        $this->published_year = $book->published_year;
        $this->level = $book->level;
        $this->old_image = $book->cover_image;

        $this->dispatch('show-modal');
    }

    public function update()
    {
        $this->course_id = $this->course_id ?: null;
        $this->validate();

        $book = Book::findOrFail($this->book_id);

        $imagePath = $this->old_image;
        if ($this->cover_image) {
            // Xóa ảnh cũ nếu có
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $this->cover_image->store('books', 'public');
        }

        $book->update([
            'title' => $this->title,
            'isbn' => $this->isbn,
            'category_id' => $this->category_id,
            'author_id' => $this->author_id,
            'publisher_id' => $this->publisher_id,
            'course_id' => $this->course_id,
            'description' => $this->description,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'published_year' => $this->published_year,
            'level' => $this->level,
            'cover_image' => $imagePath,
        ]);

        $this->dispatch('hide-modal');
        session()->flash('message', 'Cập nhật sách thành công!');
    }

    public function delete($id)
    {
        $book = Book::findOrFail($id);

        // Sách đã nằm trong đơn hàng thì giữ lại để không mất lịch sử bán hàng.
        if ($book->orderItems()->exists()) {
            session()->flash('error', 'Sách này đã có trong đơn hàng nên không thể xóa. Hãy đặt số lượng tồn kho về 0 để ngừng bán.');
            return;
        }

        if ($book->cover_image && Storage::disk('public')->exists($book->cover_image)) {
            Storage::disk('public')->delete($book->cover_image);
        }
        $book->reviews()->delete();
        $book->wishlistedBy()->delete();
        $book->images()->delete();
        $book->delete();
        session()->flash('message', 'Đã xóa sách thành công!');
    }

    public function render()
    {
        $books = Book::with(['category', 'author', 'course'])
            ->where('title', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.book-manager', [
            'books' => $books,
            'categories' => Category::all(),
            'authors' => Author::all(),
            'publishers' => Publisher::all(),
            'courses' => Course::all(),
        ]);
    }
}
