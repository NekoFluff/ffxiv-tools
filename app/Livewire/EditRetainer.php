<?php

namespace App\Livewire;

use App\Models\Retainer;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class EditRetainer extends Component
{
    public Retainer $retainer;

    public function mount(Retainer $retainer): void
    {
        $this->authorize('view', $retainer);

        $this->retainer = $retainer;
    }

    public function render(): View
    {
        return view('livewire.edit-retainer')
            ->title($this->retainer->name);
    }
}
