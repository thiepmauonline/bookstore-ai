<?php

namespace App\Livewire\Client;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Book;
use App\Models\Major;
use App\Models\Category;
use Livewire\Attributes\Url;
use App\Models\Course;
use App\Services\WishlistService;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.client')]
class Home extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';

    #[Url]
    public $search = '';
    #[Url]
    public $selectedCategory = '';
    #[Url]
    public $sort = 'newest';

    public function updatedSelectedCategory() { $this->resetPage(); }
    public function updatedSort() { $this->resetPage(); }
    public function resetFilters()
    {
        $this->reset('search', 'selectedCategory', 'selectedMajor', 'selectedCourse', 'sort');
        $this->resetPage();
    }

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
        $previousCount = $cartService->getCount();
        $cartService->add($bookId, 1);
        $this->dispatch('cart-feedback', bookId: (int) $bookId, added: $cartService->getCount() > $previousCount);
        if ($cartService->getCount() <= $previousCount) {
            return;
        }
        
        // Cập nhật sự kiện để navbar update số lượng
        $this->dispatch('cart-updated');
        session()->flash('message', 'Đã thêm sách vào giỏ hàng!');
    }

    public function toggleWishlist($bookId, WishlistService $wishlistService)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('message', 'Vui lòng đăng nhập để thêm vào danh sách yêu thích.');
        }

        $wishlistService->toggle($bookId);
        $this->dispatch('wishlist-updated');
        
        if ($wishlistService->isWishlisted($bookId)) {
            session()->flash('message', 'Đã thêm vào danh sách yêu thích!');
        } else {
            session()->flash('message', 'Đã xóa khỏi danh sách yêu thích!');
        }
    }

    public function render(WishlistService $wishlistService)
    {
        $majors = Major::all();
        $courses = collect();

        if ($this->selectedMajor) {
            $courses = Course::where('major_id', $this->selectedMajor)->get();
        }

        $books = Book::with(['author', 'category'])
            ->when($this->selectedCategory, fn ($q) => $q->where('category_id', $this->selectedCategory))
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
            ->when($this->sort === 'price_asc', fn ($q) => $q->orderBy('price'))
            ->when($this->sort === 'price_desc', fn ($q) => $q->orderByDesc('price'))
            ->orderByDesc('id')
            ->paginate(8);

        // Get wishlist status for all books
        $wishlistStatus = [];
        if (Auth::check()) {
            $wishlistItems = \App\Models\Wishlist::where('user_id', Auth::id())
                ->pluck('book_id')
                ->toArray();
            foreach ($books as $book) {
                $wishlistStatus[$book->id] = in_array($book->id, $wishlistItems);
            }
        }

        return view('livewire.client.home', [
            'books' => $books,
            'categories' => Category::withCount('books')->get(),
            'heroBooks' => Book::whereIn('title', ['Mắt Biếc', 'Tôi Thấy Hoa Vàng Trên Cỏ Xanh', 'Cho Tôi Xin Một Vé Đi Tuổi Thơ'])->get(),
            'majors' => $majors,
            'courses' => $courses,
            'wishlistStatus' => $wishlistStatus,
        ]);
    }
}
