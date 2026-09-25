<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Support\Facades\Session;

/**
 * Giỏ hàng lưu trong session dạng [book_id => item].
 * Mỗi lần đọc, giỏ được đối chiếu lại với CSDL: cập nhật giá/tồn kho mới nhất,
 * bỏ sách đã bị xóa hoặc hết hàng, giới hạn số lượng không vượt tồn kho.
 */
class CartService
{
    protected const SESSION_KEY = 'cart';

    public function getCart()
    {
        $cart = Session::get(self::SESSION_KEY, []);
        if (empty($cart)) {
            return [];
        }

        $books = Book::whereIn('id', array_keys($cart))->get()->keyBy('id');
        $synced = [];
        foreach ($cart as $bookId => $item) {
            $book = $books->get($bookId);
            $quantity = min((int) ($item['quantity'] ?? 0), $book?->quantity ?? 0);
            if (! $book || $quantity < 1) {
                continue;
            }
            $synced[$bookId] = $this->itemFor($book, $quantity);
        }

        if ($synced !== $cart) {
            Session::put(self::SESSION_KEY, $synced);
        }

        return $synced;
    }

    public function add($bookId, $quantity = 1)
    {
        $book = Book::findOrFail($bookId);
        $quantity = max(1, (int) $quantity);

        if ($book->quantity <= 0) {
            return false;
        }

        $cart = $this->getCart();
        $current = $cart[$bookId]['quantity'] ?? 0;

        // Đảm bảo không mua quá tồn kho
        $cart[$bookId] = $this->itemFor($book, min($current + $quantity, $book->quantity));

        Session::put(self::SESSION_KEY, $cart);

        return true;
    }

    public function update($bookId, $quantity)
    {
        $cart = $this->getCart();
        $quantity = (int) $quantity;

        if (isset($cart[$bookId])) {
            if ($quantity <= 0) {
                unset($cart[$bookId]);
            } else {
                $cart[$bookId]['quantity'] = min($quantity, $cart[$bookId]['max_quantity']);
            }
            Session::put(self::SESSION_KEY, $cart);
        }
    }

    public function remove($bookId)
    {
        $cart = Session::get(self::SESSION_KEY, []);
        if (isset($cart[$bookId])) {
            unset($cart[$bookId]);
            Session::put(self::SESSION_KEY, $cart);
        }
    }

    public function clear()
    {
        Session::forget(self::SESSION_KEY);
    }

    public function getTotal()
    {
        $total = 0;
        foreach ($this->getCart() as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return $total;
    }

    public function getCount()
    {
        $count = 0;
        foreach ($this->getCart() as $item) {
            $count += $item['quantity'];
        }

        return $count;
    }

    private function itemFor(Book $book, int $quantity): array
    {
        return [
            'id' => $book->id,
            'title' => $book->title,
            'price' => $book->price,
            'cover_image' => $book->cover_image,
            'quantity' => $quantity,
            'max_quantity' => $book->quantity,
        ];
    }
}
