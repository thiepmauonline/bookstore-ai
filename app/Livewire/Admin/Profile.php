<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

#[Layout('components.layouts.admin')]
class Profile extends Component
{
    public $name;
    public $email;
    public $phone;

    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    public function mount()
    {
        $user = auth('admin')->user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = auth('admin')->user();
        $user->update([
            'name' => $this->name,
            'phone' => $this->phone,
        ]);

        session()->flash('profile_message', 'Cập nhật thông tin hồ sơ thành công!');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = auth('admin')->user();

        if (!Hash::check($this->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Mật khẩu hiện tại không chính xác.',
            ]);
        }

        $user->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        
        session()->flash('password_message', 'Đổi mật khẩu thành công!');
        $this->dispatch('close-modal', modalId: 'changePasswordModal');
    }

    public function render()
    {
        return view('livewire.admin.profile');
    }
}
