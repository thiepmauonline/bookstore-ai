<?php

namespace App\Livewire\Client;

use App\Models\Book;
use App\Models\Review;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Services\CartService;
use App\Services\WishlistService;
use App\Livewire\Client\Header;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.client')]
class BookDetail extends Component
{
    public $book;
    public $quantity = 1;
    
    // Review fields
    public $rating = 5;
    public $comment = '';
    public $hasReviewed = false;

    public function mount($id)
    {
        $this->book = Book::with(['category', 'author', 'publisher', 'course'])->findOrFail($id);
        
        // Check if user has already reviewed this book
        if (Auth::check()) {
            $this->hasReviewed = Review::where('user_id', Auth::id())
                ->where('book_id', $this->book->id)
                ->exists();
        }
    }

    public function increaseQuantity()
    {
        if ($this->quantity < $this->book->quantity) {
            $this->quantity++;
        }
    }

    public function decreaseQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function addToCart(CartService $cartService)
    {
        if ($this->book->quantity < $this->quantity) {
            session()->flash('error', 'Số lượng sách trong kho không đủ.');
            return;
        }

        $previousCount = $cartService->getCount();
        $cartService->add($this->book->id, $this->quantity);
        $this->dispatch('cart-feedback', bookId: $this->book->id, added: $cartService->getCount() > $previousCount);
        if ($cartService->getCount() <= $previousCount) {
            return;
        }
        $this->dispatch('cart-updated');
        session()->flash('success', 'Đã thêm vào giỏ hàng thành công!');
    }

    public function toggleWishlist(WishlistService $wishlistService)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('message', 'Vui lòng đăng nhập để thêm vào danh sách yêu thích.');
        }

        $wishlistService->toggle($this->book->id);
        $this->dispatch('wishlist-updated');
        
        if ($wishlistService->isWishlisted($this->book->id)) {
            session()->flash('success', 'Đã thêm vào danh sách yêu thích!');
        } else {
            session()->flash('success', 'Đã xóa khỏi danh sách yêu thích!');
        }
    }

    public function submitReview()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('message', 'Vui lòng đăng nhập để đánh giá sách.');
        }

        $this->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
        ]);

        Review::create([
            'user_id' => Auth::id(),
            'book_id' => $this->book->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
        ]);

        $this->hasReviewed = true;
        $this->reset(['rating', 'comment']);
        session()->flash('success', 'Cảm ơn bạn đã đánh giá sách!');
    }

    public function render(WishlistService $wishlistService)
    {
        $relatedBooks = Book::where('category_id', $this->book->category_id)
            ->where('id', '!=', $this->book->id)
            ->limit(4)
            ->get();

        $isWishlisted = Auth::check() ? $wishlistService->isWishlisted($this->book->id) : false;

        // Get reviews for this book
        $reviews = Review::where('book_id', $this->book->id)
            ->with('user')
            ->latest()
            ->paginate(5);

        // Calculate average rating
        $averageRating = Review::where('book_id', $this->book->id)->avg('rating') ?? 0;
        $totalReviews = Review::where('book_id', $this->book->id)->count();

        return view('livewire.client.book-detail', [
            'relatedBooks' => $relatedBooks,
            'isWishlisted' => $isWishlisted,
            'reviews' => $reviews,
            'averageRating' => round($averageRating, 1),
            'totalReviews' => $totalReviews,
        ]);
    }
}
