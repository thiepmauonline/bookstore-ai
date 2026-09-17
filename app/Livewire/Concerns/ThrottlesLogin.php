<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

trait ThrottlesLogin
{
    protected function loginRateKey(string $guard): string
    {
        return 'login:'.$guard.':'.hash('sha256', Str::lower($this->email).'|'.request()->ip());
    }

    protected function loginIsThrottled(string $guard): bool
    {
        $key = $this->loginRateKey($guard);
        if (! RateLimiter::tooManyAttempts($key, 5)) {
            return false;
        }

        $this->addError('email', 'Bạn thử đăng nhập quá nhiều lần. Vui lòng thử lại sau '.RateLimiter::availableIn($key).' giây.');

        return true;
    }
}
