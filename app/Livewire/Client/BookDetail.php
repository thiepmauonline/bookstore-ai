<?php

namespace App\Livewire\Client;

use App\Models\Book;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Services\CartService;
use App\Livewire\Client\Header;

#[Layout('components.layouts.client')]
class BookDetail extends Component
{
    public $book;
    public $quantity = 1;

    public function mount($id)
    {
        $this->book = Book::with(['category', 'author', 'publisher', 'course'])->findOrFail($id);
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

        $cartService->add($this->book, $this->quantity);
        $this->dispatch('cart-updated')->to(Header::class);
        session()->flash('success', 'Đã thêm vào giỏ hàng thành công!');
    }

    public function render()
    {
        $relatedBooks = Book::where('category_id', $this->book->category_id)
            ->where('id', '!=', $this->book->id)
            ->limit(4)
            ->get();

        return view('livewire.client.book-detail', [
            'relatedBooks' => $relatedBooks
        ]);
    }
}
