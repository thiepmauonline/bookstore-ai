<?php

namespace Tests\Feature;

use App\Livewire\Admin\ReviewManager;
use App\Livewire\Admin\Settings;
use App\Livewire\Client\BookDetail;
use App\Livewire\Client\Contact;
use App\Models\Book;
use App\Models\Contact as ContactMessage;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StoreManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'active']);
    }

    public function test_contact_form_is_saved_for_admin(): void
    {
        Livewire::test(Contact::class)
            ->set('name', 'Nguyễn Văn A')
            ->set('email', 'a@example.com')
            ->set('message', 'Shop có bán giáo trình Toán cao cấp không?')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contacts', ['email' => 'a@example.com', 'is_handled' => false]);

        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.contacts'))
            ->assertOk()
            ->assertSee('Toán cao cấp');
    }

    public function test_contact_form_validates_input(): void
    {
        Livewire::test(Contact::class)
            ->set('name', '')->set('email', 'not-an-email')->set('message', 'ngắn')
            ->call('submit')
            ->assertHasErrors(['name', 'email', 'message']);

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_admin_settings_are_saved_and_shown_on_storefront(): void
    {
        $this->actingAs($this->admin(), 'admin');

        Livewire::test(Settings::class)
            ->set('form.hotline', '1900 1234')
            ->set('form.store_name', 'Nhà sách Sinh Viên')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('1900 1234', Setting::get('hotline'));
        $this->get(route('contact'))->assertOk()->assertSee('1900 1234')->assertSee('Nhà sách Sinh Viên');
    }

    public function test_settings_reject_invalid_email(): void
    {
        $this->actingAs($this->admin(), 'admin');

        Livewire::test(Settings::class)->set('form.email', 'sai-email')->call('save')->assertHasErrors('form.email');
    }

    public function test_customer_reviews_book_once_and_hidden_reviews_are_not_public(): void
    {
        $book = Book::create(['title' => 'Clean Code', 'price' => 100000, 'quantity' => 3]);
        $customer = User::factory()->create(['role' => 'user', 'status' => 'active']);
        $this->actingAs($customer);

        Livewire::test(BookDetail::class, ['id' => $book->id])
            ->set('rating', 5)->set('comment', 'Sách rất hay và dễ hiểu')
            ->call('submitReview')->assertHasNoErrors();
        Livewire::test(BookDetail::class, ['id' => $book->id])
            ->set('rating', 1)->set('comment', 'Đánh giá lần hai không được lưu')
            ->call('submitReview');

        $review = Review::sole();
        $this->assertSame(5, $review->rating);

        $this->actingAs($this->admin(), 'admin');
        Livewire::test(ReviewManager::class)->call('toggleVisibility', $review->id);
        $this->assertFalse($review->fresh()->is_visible);

        $this->get(route('book.detail', $book->id))->assertOk()->assertDontSee('Sách rất hay và dễ hiểu');
    }

    public function test_admin_locks_customer_instead_of_deleting_one_with_orders(): void
    {
        $customer = User::factory()->create(['role' => 'user', 'status' => 'active']);
        \App\Models\Order::create([
            'user_id' => $customer->id, 'order_code' => 'DHTEST1', 'total_price' => 100000,
        ]);
        $this->actingAs($this->admin(), 'admin');

        Livewire::test(\App\Livewire\Admin\UserManager::class)
            ->call('delete', $customer->id)
            ->assertSee('không thể xóa');
        $this->assertModelExists($customer);

        Livewire::test(\App\Livewire\Admin\UserManager::class)->call('toggleStatus', $customer->id);
        $this->assertSame('banned', $customer->fresh()->status);
    }

    public function test_new_admin_pages_render(): void
    {
        $this->seed();
        $this->actingAs($this->admin(), 'admin');

        foreach (['admin.dashboard', 'admin.orders', 'admin.reviews', 'admin.contacts', 'admin.chatbot', 'admin.settings'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }
}
