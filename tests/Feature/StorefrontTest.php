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

    public function test_book_reviews_can_change_pages_and_return_to_first_page_after_posting(): void
    {
        $book = Book::create(['title' => 'Giáo trình Web', 'price' => 100000, 'quantity' => 5]);
        foreach (\App\Models\User::factory()->count(6)->create() as $index => $user) {
            \App\Models\Review::create([
                'user_id' => $user->id, 'book_id' => $book->id,
                'rating' => 5, 'comment' => 'Đánh giá mẫu số '.($index + 1),
            ]);
        }
        $this->actingAs(\App\Models\User::factory()->create(['role' => 'user', 'status' => 'active']));
        Livewire::test(\App\Livewire\Client\BookDetail::class, ['id' => $book->id])
            ->assertSee('Đánh giá mẫu số 6')->assertDontSee('Đánh giá mẫu số 1')
            ->call('gotoPage', 2)
            ->assertSee('Đánh giá mẫu số 1')->assertDontSee('Đánh giá mẫu số 6')
            ->set('rating', 5)->set('comment', 'Đánh giá mới sau khi chuyển trang')
            ->call('submitReview')->assertHasNoErrors()
            ->assertSet('paginators.page', 1)
            ->assertSee('Đánh giá mới sau khi chuyển trang');
    }

}
