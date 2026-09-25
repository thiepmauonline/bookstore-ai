<?php

namespace App\Livewire\Client;

use App\Models\Contact as ContactMessage;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.client')]
class Contact extends Component
{
    /** Số tin nhắn tối đa mỗi IP trong một giờ, chống spam form. */
    private const MAX_PER_HOUR = 5;

    public $name;
    public $email;
    public $message;

    public function mount()
    {
        if ($user = auth()->user()) {
            $this->name = $user->name;
            $this->email = $user->email;
        }
    }

    public function submit()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|min:10|max:2000',
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'message.required' => 'Vui lòng nhập nội dung.',
            'message.min' => 'Nội dung cần ít nhất 10 ký tự.',
        ]);

        $key = 'contact:'.request()->ip();
        if (RateLimiter::tooManyAttempts($key, self::MAX_PER_HOUR)) {
            $this->addError('message', 'Bạn đã gửi quá nhiều tin nhắn. Vui lòng thử lại sau.');
            return;
        }
        RateLimiter::hit($key, 3600);

        ContactMessage::create([
            'user_id' => auth()->id(),
            'name' => $this->name,
            'email' => $this->email,
            'message' => $this->message,
        ]);

        session()->flash('contact_message', 'Cảm ơn bạn đã liên hệ. Chúng tôi sẽ phản hồi sớm nhất có thể!');
        $this->reset('message');
    }

    public function render()
    {
        return view('livewire.client.contact');
    }
}
