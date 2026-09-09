<?php

namespace App\Livewire\Client;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Wishlist as WishlistModel;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.client')]
class Wishlist extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';

    public function removeFromWishlist($bookId)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('message', 'Vui lòng đăng nhập để tiếp tục.');
        }

        $wishlist = WishlistModel::where('user_id', Auth::id())
            ->where('book_id', $bookId)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            session()->flash('message', 'Đã xóa khỏi danh sách yêu thích!');
            $this->dispatch('wishlist-updated');
        }
    }

    public function addToCart($bookId, \App\Services\CartService $cartService)
    {
        $cartService->add($bookId, 1);
        $this->dispatch('cart-updated');
        session()->flash('message', 'Đã thêm sách vào giỏ hàng!');
    }

    public function render()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('message', 'Vui lòng đăng nhập để xem danh sách yêu thích.');
        }

        $wishlistItems = WishlistModel::where('user_id', Auth::id())
            ->with('book')
            ->latest()
            ->paginate(12);

        return view('livewire.client.wishlist', [
            'wishlistItems' => $wishlistItems
        ]);
    }
}
