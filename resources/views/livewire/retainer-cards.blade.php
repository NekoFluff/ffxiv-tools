<div class="py-12">
    <div class="grid grid-cols-1 gap-5 mx-auto lg:grid-cols-3 max-w-7xl sm:px-6 lg:px-8">
        @foreach ($retainers as $retainer)
            <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                <div class="p-6 text-lg text-gray-900 dark:text-gray-100">
                    {{ $retainer->name }}

                    {{-- Descriptive Row --}}
                    <div class="flex justify-between mt-4 text-sm">
                        Item
                        <x-pill label="Market Price" value="Listing Price" color="indigo" />
                    </div>

                    @foreach ($retainer->items as $item)
                        <div class="flex justify-between mt-4 text-sm">
                            <a href="{{ route('item.show', $item->id) }}">{{ $item->name }}</a>
                            <x-pill label="{{ $item->marketPrice($retainer->server)?->price ?? 'No market price' }}"
                                value="{{ $retainer->getListingPrice($item) ?? 'No listing price' }}"
                                color="{{ $item->marketPrice($retainer->server)?->price <= $retainer->getListingPrice($item) ? 'red' : 'green' }}" />
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
