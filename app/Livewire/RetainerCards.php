<?php

namespace App\Livewire;

use App\Models\Retainer;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Component;

#[Lazy(isolate: false)]
class RetainerCards extends Component
{
    /**
     * The retainer collection.
     *
     * @var Collection<int,Retainer>
     */
    public Collection $retainers;

    public function mount(): void
    {
        $this->refreshRetainers();
    }

    #[On('retainer-created')]
    public function refreshRetainers(): void
    {
        /** @var User $user */
        $user = auth()->user();

        $this->retainers = $user->retainers()->with('listings', 'items')->get();
    }

    public function placeholder(): View
    {
        return view('livewire.placeholders.retainer-cards');
    }

    public function render(): View
    {
        return view('livewire.retainer-cards');
    }
}
