<div>
    <flux:input type="text" placeholder="Search..." wire:model.live.debounce.250ms="search" />

    <div x-data="{ open: $wire.entangle('showItems').live }" class="relative z-10">
        <div x-show="open" class="absolute w-full p-2 mt-2 overflow-auto bg-white rounded-md max-h-96 scrollbar"
            @click.outside="open = false; console.log(open); console.log($wire.items)">
            @foreach ($items as $item)
                <a wire:key="{{ $item->id }}"
                    class="flex flex-row items-center w-full p-2 rounded-md hover:bg-zinc-100"
                    href="{{ route('item.show', $item->id) }}">
                    <img class="inline w-6 h-6" src="{{ 'https://xivapi.com/' . $item->icon }}" />
                    <span class="ml-2 text-sm text-black">{{ $item->name }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>
