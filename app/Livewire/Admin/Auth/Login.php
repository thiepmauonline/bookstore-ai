<?php

namespace App\Livewire\Admin\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.admin-auth')]
class Login extends Component
{
    public $email;
    public $password;
    public $remember = false;

    public function login()
    {
        $credentials = $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt($credentials, $this->remember)) {
            if (Auth::user()->role === 'admin') {
                session()->regenerate();
                return redirect()->intended(route('admin.dashboard'));
            } else {
                Auth::logout();
                $this->addError('email', 'Bạn không có quyền truy cập vào khu vực này.');
            }
        } else {
            $this->addError('email', 'Email hoặc mật khẩu không chính xác.');
        }
    }

    public function render()
    {
        return view('livewire.admin.auth.login');
    }
}
