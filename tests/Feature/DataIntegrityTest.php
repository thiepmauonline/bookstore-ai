<?php

namespace Tests\Feature;

use App\Livewire\Admin\AuthorManager;
use App\Livewire\Admin\BookManager;
use App\Livewire\Admin\CategoryManager;
use App\Livewire\Admin\MajorManager;
use App\Livewire\Admin\OrderManager;
use App\Livewire\Admin\PublisherManager;
use App\Livewire\Auth\Login;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Major;
use App\Models\OrderItem;
use App\Models\Publisher;
use App\Models\User;
use App\Services\CartService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * CSDL không dùng khóa ngoại, nên ứng dụng phải tự giữ toàn vẹn dữ liệu.
 */
class DataIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('role', 'admin')->firstOrFail(), 'admin');
    }

    public function test_lookup_data_in_use_cannot_be_deleted(): void
    {
        $cases = [
            [CategoryManager::class, Category::first()],
            [AuthorManager::class, Author::first()],
            [PublisherManager::class, Publisher::first()],
            [MajorManager::class, Major::first()],
        ];

        foreach ($cases as [$component, $model]) {
            Livewire::test($component)->call('delete', $model->id)->assertSee('Không thể xóa');
            $this->assertModelExists($model);
        }

        $this->get('/?page=4')->assertOk();
    }

    public function test_unused_category_can_be_deleted(): void
    {
        $category = Category::create(['name' => 'Tạm', 'slug' => 'tam']);

        Livewire::test(CategoryManager::class)->call('delete', $category->id);

        $this->assertModelMissing($category);
    }

    public function test_sold_book_is_kept_and_order_details_still_render(): void
    {
        $item = OrderItem::firstOrFail();

        Livewire::test(BookManager::class)->call('delete', $item->book_id)->assertSee('không thể xóa');
        $this->assertTrue(Book::whereKey($item->book_id)->exists());

        Livewire::test(OrderManager::class)->call('viewDetails', $item->order_id)->assertOk();
    }

    public function test_reference_book_without_course_can_be_saved(): void
    {
        $book = Book::whereNull('course_id')->firstOrFail();

        Livewire::test(BookManager::class)
            ->call('edit', $book->id)
            ->set('price', 99000)
            ->call('update')
            ->assertHasNoErrors();

        $this->assertEquals(99000, $book->fresh()->price);
        $this->assertNull($book->fresh()->course_id);
    }

    public function test_cart_ignores_invalid_quantity_and_out_of_stock_books(): void
    {
        $cart = app(CartService::class);
        $inStock = Book::where('quantity', '>', 5)->firstOrFail();
        $soldOut = Book::where('quantity', 0)->firstOrFail();

        $cart->add($inStock->id, -5);
        $this->assertSame(1, $cart->getCount(), 'Số lượng âm được coi là 1.');

        $this->assertFalse($cart->add($soldOut->id));
        $this->assertArrayNotHasKey($soldOut->id, $cart->getCart());

        // Admin tăng giá sau khi khách bỏ vào giỏ: giỏ hiển thị giá mới.
        $inStock->update(['price' => 777000]);
        $this->assertEquals(777000, $cart->getTotal());
    }

    public function test_locked_customer_sees_locked_message(): void
    {
        Livewire::test(Login::class)
            ->set('email', 'locked@bookstore.test')
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors('email')
            ->assertSee('đã bị khóa');

        Livewire::test(Login::class)
            ->set('email', 'locked@bookstore.test')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertSee('không chính xác');
    }
}
