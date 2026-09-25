<?php

namespace App\Livewire\Client;

use App\Models\Book;
use App\Models\Course;
use App\Models\Major;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.client')]
class About extends Component
{
    public function render()
    {
        return view('livewire.client.about', [
            'bookCount' => Book::count(),
            'majorCount' => Major::count(),
            'courseCount' => Course::count(),
        ]);
    }
}
