<?php

use App\Models\Retainer;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public Retainer $retainer;

    public function mount($retainer): void
    {
        $this->retainer = $retainer;
    }

    #[On('retainer-item-added')]
    public function reloadItems(): void
    {
        $this->retainer->load('items');
    }

    public function removeItem(int $id): void
    {
        $this->authorize('update', $this->retainer);

        $this->retainer->items()->detach($id);

        $this->reloadItems();
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">
            {{ __('Items') }}
        </h2>

        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Add or remove items from your retainer.') }}
        </p>
    </header>

    <livewire:retainer.add-retainer-item-form :retainer="$retainer" />

    <ul class="p-2 mt-5 space-y-2">
        {{-- Items --}}
        @foreach ($retainer->items as $item)
            <li>
                <div class="flex items-center justify-between">
                    <div class="flex">
                        <img class="w-6 h-6" src="{{ 'https://xivapi.com/' . $item->icon }}" />
                        <span class="ml-2 dark:text-white">{{ $item->name }}</span>
                    </div>
                    <div>
                        <flux:button type="submit" variant="danger" class="uppercase"
                            wire:click="removeItem({{ $item->id }})">Remove</flux:button>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>

</section>
