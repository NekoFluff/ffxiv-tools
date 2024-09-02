<div class="py-12">
    <div class="grid grid-cols-1 gap-5 mx-auto lg:grid-cols-3 max-w-7xl sm:px-6 lg:px-8">
        @foreach ($retainers as $retainer)
            <div wire:key="{{ $retainer->id }}" class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                <div class="p-6 text-lg text-gray-900 dark:text-gray-100">
                    <div class="flex items-center justify-between">
                        <div>{{ $retainer->name }}<span
                                class="ml-2 text-xs dark:text-gray-100">[{{ $retainer->server }}]</span></div>
                        <a href="{{ route('retainer.edit', $retainer->id) }}">
                            <x-heroicon-s-pencil class="w-5 h-5" />
                        </a>
                    </div>


                    {{-- Descriptive Row --}}
                    <div class="flex justify-between mt-4 text-sm">
                        Item
                        <x-pill label="Market Price" value="Listing Price" color="indigo" />
                    </div>

                    @foreach ($retainer->items as $item)
                        <div wire:key="{{ $item->id }}" class="flex justify-between mt-4 text-sm">
                            <a href="{{ route('item.show', $item->id) }}">{{ $item->name }}</a>
                            <x-pill label="{{ $item->marketPrice($retainer->server)?->price ?? 'No market price' }}"
                                value="{{ $retainer->getListingPrice($item) ?? 'No listing price' }}"
                                color="{{ ($item->marketPrice($retainer->server)?->price <= $retainer->getListingPrice($item) ? 'red' : !$retainer->getListingPrice($item)) ? 'red' : 'green' }}" />
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
