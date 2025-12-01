<?php

namespace App\Http\Livewire;

use Livewire\Component;

class MyComponent extends Component
{
    public $message = 'Hello, Livewire!';

    public function render()
    {
        return view('livewire.my-component');
    }

    public function updateMessage()
    {
        $this->message = 'Message updated!';
    }
}
