<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Dashboard;
use App\Http\Middleware\AdminMiddleware;
use App\Livewire\Client\Home;
use App\Livewire\Client\Cart;
use App\Livewire\Client\Checkout;
use App\Livewire\Client\About;
use App\Livewire\Client\Contact;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Admin\Auth\Login as AdminLogin;

use App\Livewire\Admin\BookManager;
use App\Livewire\Admin\CategoryManager;
use App\Livewire\Admin\AuthorManager;
use App\Livewire\Admin\PublisherManager;
use App\Livewire\Admin\MajorManager;
use App\Livewire\Admin\CouponManager;
use App\Livewire\Admin\OrderManager;
use App\Livewire\Admin\UserManager;

Route::get('/', Home::class)->name('home');
Route::get('/book/{id}', \App\Livewire\Client\BookDetail::class)->name('book.detail');
Route::get('/about', About::class)->name('about');
Route::get('/contact', Contact::class)->name('contact');
Route::get('/cart', Cart::class)->name('cart');
Route::get('/checkout', Checkout::class)->name('checkout');
Route::middleware('auth:web')->group(function () {
    Route::get('/my-orders', \App\Livewire\Client\MyOrders::class)->name('my.orders');
    Route::get('/wishlist', \App\Livewire\Client\Wishlist::class)->name('wishlist');
});

Route::prefix('admin')->group(function () {
    Route::get('/login', AdminLogin::class)->name('admin.login');
    
    Route::post('/logout', [\App\Http\Controllers\LogoutController::class, 'admin'])->name('admin.logout');

    Route::middleware([AdminMiddleware::class])->group(function () {
        Route::get('/', Dashboard::class)->name('admin.dashboard');
        Route::get('/books', BookManager::class)->name('admin.books');
        Route::get('/categories', CategoryManager::class)->name('admin.categories');
        Route::get('/authors', AuthorManager::class)->name('admin.authors');
        Route::get('/publishers', PublisherManager::class)->name('admin.publishers');
        Route::get('/majors', MajorManager::class)->name('admin.majors');
        Route::get('/coupons', CouponManager::class)->name('admin.coupons');
        Route::get('/orders', OrderManager::class)->name('admin.orders');
        Route::get('/users', UserManager::class)->name('admin.users');
        Route::get('/profile', \App\Livewire\Admin\Profile::class)->name('admin.profile');
    });
});

Route::get('/login', Login::class)->name('login');
Route::get('/register', Register::class)->name('register');
Route::post('/logout', [\App\Http\Controllers\LogoutController::class, 'client'])->name('logout');
