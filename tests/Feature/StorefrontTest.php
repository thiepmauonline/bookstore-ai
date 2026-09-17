<?php

namespace Tests\Feature;

use App\Livewire\Client\Home;
use App\Models\Book;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_filters_sorting_and_cart_work(): void
    {
        $this->seed(DatabaseSeeder::class);
        $book = Book::where('title', 'like', 'Laravel%')->firstOrFail();
        Livewire::withQueryParams(['search' => 'Laravel'])->test(Home::class)
            ->assertSet('search', 'Laravel')
            ->assertViewHas('books', fn ($books) => $books->total() === 1)
            ->call('addToCart', $book->id)
            ->assertDispatched('cart-updated')
            ->assertDispatched('cart-feedback', bookId: $book->id, added: true)
            ->call('resetFilters')
            ->set('selectedCategory', $book->category_id)
            ->assertViewHas('books', fn ($books) => $books->total() === 5)
            ->set('sort', 'price_asc')
            ->assertViewHas('books', fn ($books) => $books->first()->price == $books->min('price'));
        $this->get('/cart')->assertOk()->assertSee($book->title);
        $this->get('/book/'.$book->id)->assertOk()->assertSee($book->title);
    }

    public function test_search_empty_state_can_be_reset(): void
    {
        $this->seed(DatabaseSeeder::class);
        Livewire::test(Home::class)->set('search', 'no-such-book-xyz')
            ->assertSee('Chưa tìm thấy cuốn sách phù hợp')
            ->call('resetFilters')
            ->assertViewHas('books', fn ($books) => $books->total() === 30);
    }
    public function test_detail_adds_to_cart_and_reports_stock_limit(): void
    {
        $this->seed(DatabaseSeeder::class);
        $book = Book::firstOrFail();
        $book->update(['quantity' => 1]);
        Livewire::test(\App\Livewire\Client\BookDetail::class, ['id' => $book->id])
            ->call('addToCart')
            ->assertDispatched('cart-feedback', bookId: $book->id, added: true)
            ->assertDispatched('cart-updated')
            ->call('addToCart')
            ->assertDispatched('cart-feedback', bookId: $book->id, added: false);
        $this->assertSame(1, app(\App\Services\CartService::class)->getCount());
    }

}
