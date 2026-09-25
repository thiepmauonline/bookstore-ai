<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Livewire\Livewire::addPersistentMiddleware([\App\Http\Middleware\AdminMiddleware::class]);

        // Thông tin cửa hàng (tên, hotline, email...) do admin cấu hình, dùng chung cho các layout.
        View::composer(
            ['components.layouts.*', 'livewire.client.*'],
            fn ($view) => $view->with('settings', Setting::allValues())
        );
    }
}
