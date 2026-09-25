<?php

namespace App\Livewire\Auth;

use App\Livewire\Concerns\ThrottlesLogin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.client')]
class Login extends Component
{
    use ThrottlesLogin;

    public $email;

    public $password;

    public $remember = false;

    public function login()
    {
        $credentials = $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($this->loginIsThrottled('web')) {
            return;
        }

        if (Auth::guard('web')->attempt([...$credentials, 'role' => 'user', 'status' => 'active'], $this->remember)) {
            RateLimiter::clear($this->loginRateKey('web'));
            // SessionGuard rotates the session ID and CSRF token on login.

            return redirect()->intended(route('home'));
        }

        RateLimiter::hit($this->loginRateKey('web'), 60);

        // Chỉ báo "bị khóa" khi mật khẩu đúng, để không lộ email nào đã đăng ký.
        $isLocked = Auth::guard('web')->validate([...$credentials, 'role' => 'user'])
            && User::where('email', $credentials['email'])->value('status') !== 'active';

        $this->addError('email', $isLocked
            ? 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ cửa hàng để được hỗ trợ.'
            : 'Thông tin đăng nhập không chính xác.');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
