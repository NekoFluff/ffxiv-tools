<?php

use App\Models\Item;
use App\Models\Retainer;
use Livewire\Volt\Component;

new class extends Component
{
    public Retainer $retainer;

    /**
     * @var array<Item>
     */
    public array $selectableItems = [];

    public int $selectedItemID = -1;

    public string $search = '';

    public function mount($retainer): void
    {
        $this->retainer = $retainer;
    }

    public function addItem(): void
    {
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
    }
}; ?>

<section x-data="{
    selectItem(item) {
        $wire.selectedItemID = item.id;
        $wire.selectableItems = [];
        $wire.search = item.name;
    }
}">
    <form wire:submit="addItem" class="flex items-center justify-between mt-4 mb-8 mr-2">
        <div class="flex-grow">
            <input
                class="w-full p-2 text-black bg-white border-gray-300 rounded-md shadow-md outline-none placeholder-slate-700 shadow-grey-900"
                type="text" placeholder="Search..." wire:model.live.debounce.250ms="search" />

            <div class="relative z-10">
                <div class="absolute w-full overflow-auto bg-blue-500 max-h-96 scrollbar">
                    <template x-for="item in $wire.selectableItems" :key="item.id">
                        <button x-on:click="selectItem(item)"
                            class="flex flex-row items-center w-full p-3 rounded-md hover:bg-blue-600">
                            <img class="inline w-6 h-6" :src="'https://xivapi.com/' + item.icon" />
                            <span class="ml-2 text-sm text-white" x-text="item.name"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
        <x-primary-button class="ml-5 px-7">Add</x-primary-button>
        <x-input-error class="mt-1" :messages="$errors->get('search')" />
    </form>
</section>
