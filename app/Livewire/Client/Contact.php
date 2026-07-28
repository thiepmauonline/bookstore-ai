<?php

namespace App\Livewire\Client;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.client')]
class Contact extends Component
{
    public $name;
    public $email;
    public $message;

    public function submit()
    {
        $this->validate([
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required'
        ]);

        session()->flash('message', 'Cảm ơn bạn đã liên hệ. Chúng tôi sẽ phản hồi sớm nhất có thể!');
        $this->reset(['name', 'email', 'message']);
    }

    public function render()
    {
        return view('livewire.client.contact');
    }
}
