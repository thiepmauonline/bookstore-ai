<?php

namespace App\Livewire\Admin\Auth;

use App\Livewire\Concerns\ThrottlesLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin-auth')]
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
            'password' => 'required|min:6',
        ]);

        if ($this->loginIsThrottled('admin')) {
            return;
        }

        if (Auth::guard('admin')->attempt([...$credentials, 'role' => 'admin', 'status' => 'active'], $this->remember)) {
            RateLimiter::clear($this->loginRateKey('admin'));

            // SessionGuard rotates the session ID and CSRF token on login.
            return redirect()->route('admin.dashboard');
        }

        RateLimiter::hit($this->loginRateKey('admin'), 60);
        $this->addError('email', 'Email hoặc mật khẩu không chính xác, hoặc tài khoản không có quyền quản trị.');
    }

    public function render()
    {
        return view('livewire.admin.auth.login');
    }
}
