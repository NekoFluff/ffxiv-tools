<div>
    <input
        class="w-full p-2 text-black bg-white border-gray-300 rounded-md shadow-md outline-none placeholder-slate-700 shadow-grey-900"
        type="text" placeholder="Search..." wire:model.live.debounce.150ms="search" />

    <div class="relative z-10">
        <div class="absolute w-full overflow-auto bg-blue-500 max-h-96 scrollbar">
            @foreach ($items as $item)
                <a class="flex flex-row items-center w-full p-3 rounded-md hover:bg-blue-600"
                    href="{{ route('item.show', $item->id) }}">
                    <img class="inline w-6 h-6" src="{{ 'https://xivapi.com/' . $item->icon }}" />
                    <span class="ml-2 text-sm text-white">{{ $item->name }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>
