<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Recipe extends Component
{
    public function render(): View
    {
        return view('livewire.recipe');
    }
}
