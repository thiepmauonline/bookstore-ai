<?php

namespace App\Services;

use App\Models\Book;
use Illuminate\Support\Facades\Session;

class CartService
{
    protected const SESSION_KEY = 'cart';

    public function getCart()
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public function add($bookId, $quantity = 1)
    {
        $cart = $this->getCart();
        $book = Book::findOrFail($bookId);

        if (isset($cart[$bookId])) {
            // Tăng số lượng nếu đã có trong giỏ
            $cart[$bookId]['quantity'] += $quantity;
        } else {
            // Thêm mới
            $cart[$bookId] = [
                'id' => $book->id,
                'title' => $book->title,
                'price' => $book->price,
                'cover_image' => $book->cover_image,
                'quantity' => $quantity,
                'max_quantity' => $book->quantity, // Để validate lúc checkout
            ];
        }

        // Đảm bảo không mua quá tồn kho
        if ($cart[$bookId]['quantity'] > $book->quantity) {
            $cart[$bookId]['quantity'] = $book->quantity;
        }

        Session::put(self::SESSION_KEY, $cart);
        return true;
    }

    public function update($bookId, $quantity)
    {
        $cart = $this->getCart();
        
        if (isset($cart[$bookId])) {
            if ($quantity <= 0) {
                unset($cart[$bookId]);
            } else {
                $book = Book::findOrFail($bookId);
                $cart[$bookId]['quantity'] = min($quantity, $book->quantity);
            }
            Session::put(self::SESSION_KEY, $cart);
        }
    }

    public function remove($bookId)
    {
        $cart = $this->getCart();
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
        $cart = $this->getCart();
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public function getCount()
    {
        $cart = $this->getCart();
        $count = 0;
        foreach ($cart as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }
}
