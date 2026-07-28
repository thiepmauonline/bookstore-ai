<?php

namespace App\Livewire\Client;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.client')]
class About extends Component
{
    public function render()
    {
        return view('livewire.client.about');
    }
}
