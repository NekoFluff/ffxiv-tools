<?php

namespace App\Livewire;

use App\Livewire\Forms\RetainerForm;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateRetainer extends Component
{
    public RetainerForm $form;

    public function createRetainer(): void
    {
        if ($this->form->store()) {
            $this->dispatch('retainer-created');
        }
    }

    public function render(): View
    {
        return view('livewire.create-retainer');
    }
}
