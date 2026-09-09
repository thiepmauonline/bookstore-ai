<?php

namespace App\Services;

use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;

class WishlistService
{
    /**
     * Check if a book is in user's wishlist
     */
    public function isWishlisted($bookId): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return Wishlist::where('user_id', Auth::id())
            ->where('book_id', $bookId)
            ->exists();
    }

    /**
     * Add book to wishlist
     */
    public function add($bookId): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $exists = Wishlist::where('user_id', Auth::id())
            ->where('book_id', $bookId)
            ->exists();

        if (!$exists) {
            Wishlist::create([
                'user_id' => Auth::id(),
                'book_id' => $bookId,
            ]);
            return true;
        }

        return false;
    }

    /**
     * Remove book from wishlist
     */
    public function remove($bookId): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $wishlist = Wishlist::where('user_id', Auth::id())
            ->where('book_id', $bookId)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            return true;
        }

        return false;
    }

    /**
     * Toggle wishlist (add if not exists, remove if exists)
     */
    public function toggle($bookId): bool
    {
        if ($this->isWishlisted($bookId)) {
            return $this->remove($bookId);
        } else {
            return $this->add($bookId);
        }
    }

    /**
     * Get wishlist count for current user
     */
    public function getCount(): int
    {
        if (!Auth::check()) {
            return 0;
        }

        return Wishlist::where('user_id', Auth::id())->count();
    }
}
