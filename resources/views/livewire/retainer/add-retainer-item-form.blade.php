<?php

use App\Models\Item;
use App\Models\Retainer;
use Livewire\Volt\Component;

new class extends Component {
    public Retainer $retainer;

    /**
     * @var array<Item>
     */
    public array $selectableItems = [];

    public int $selectedItemID = -1;

    public string $search = '';

    public bool $showItems = false;

    public function mount($retainer): void
    {
        $this->retainer = $retainer;
    }

    public function addItem(): void
    {
        $this->showItems = false;
        $this->selectableItems = [];

        $this->authorize('update', $this->retainer);

        $this->validate([
            'search' => ['required', 'string', 'max:255'],
        ]);

        $item = Item::where('id', $this->selectedItemID)
            ->orWhere('name', $this->search)
            ->first();

        if ($item === null) {
            $this->addError('search', 'Item not found.');

            return;
        }

        if ($this->retainer->items->contains($item)) {
            $this->addError('search', 'Item already added.');

            return;
        }

        if ($this->retainer->items->count() >= 20) {
            $this->addError('search', 'You can only add up to 20 items.');

            return;
        }

        $this->retainer->items()->attach($item);

        $this->search = '';

        $this->dispatch('retainer-item-added', name: $item->name);
    }

    public function updatedSearch(): void
    {
        $this->selectedItemID = -1;
        $this->selectableItems = Item::where('name', 'like', "%{$this->search}%")
            ->limit(10)
            ->get()
            ->toArray();
        $this->showItems = !empty($this->selectableItems);
    }
}; ?>

<section class="mt-4 mb-8 mr-2" x-data="{
    selectItem(item) {
        $wire.selectedItemID = item.id;
        $wire.selectableItems = [];
        $wire.search = item.name;
        $wire.showItems = false;
    }
}">
    <form wire:submit="addItem" class="flex items-center justify-between">
        <div class="flex-grow">
            <flux:input type="text" placeholder="Search..." wire:model.live.debounce.250ms="search" />

            <div x-data="{ open: $wire.entangle('showItems') }" class="relative z-10">
                <div x-show="open"
                    class="absolute w-full p-2 mt-2 overflow-auto bg-white rounded-md max-h-96 scrollbar"
                    @click.outside="open = false; console.log(open); console.log($wire.items)">
                    <template x-for="item in $wire.selectableItems" :key="item.id">
                        <button x-on:click="selectItem(item); open = false"
                            class="flex flex-row items-center w-full p-2 rounded-md hover:bg-zinc-100">
                            <img class="inline w-6 h-6" :src="'https://xivapi.com/' + item.icon" />
                            <span class="ml-2 text-sm text-black" x-text="item.name"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
        <flux:button type="submit" variant="primary" class="ml-5 px-7">ADD</flux:button>
    </form>
    <flux:error class="mt-1" name="search" />
</section>
