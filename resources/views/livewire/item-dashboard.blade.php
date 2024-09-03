@once
    {{-- TODO: I installed ChartJS so I shouldn't need to pull it from CDN. Not sure how to do it yet --}}
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush
@endonce

<div class="text-gray-800 dark:text-gray-200">
    <x-slot name="header">
        <div class="flex items-center">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                {{ $item ? $item->name : 'Search for an Item' }}
            </h2>
            <div class="flex-1 mx-7">
                <livewire:item-search-bar />
            </div>
            <livewire:server-dropdown />
        </div>
    </x-slot>

    <div class="mx-auto mt-5 max-w-7xl sm:px-6 lg:px-8">
        @if ($item)
            <div class="mb-10">
                <livewire:item-boxes :$item />
            </div>
            <div class="grid grid-cols-1 gap-10 mb-10 lg:grid-cols-2">
                <div>
                    <livewire:quantity-sold-chart :itemID="$item->id" />
                </div>
                <div>
                    <livewire:price-history-chart :itemID="$item->id" />
                </div>
            </div>
            <div class="pb-20">
                <livewire:current-listings-table :itemID="$item->id" />
            </div>
        @else
            <div>
                <h2 class="text-lg ">The item <span class="font-bold">{{ $id }}</span> doesn't seem to exist
                    in our system.</h2>
                <p class="text-sm">We'll try to fetch the item data for you. If there is an actual item associated with
                    the ID you provided, you should be able to refresh the page in a minute and additional data will
                    appear. Otherwise check to make sure you've input the correct item ID.</p>
            </div>
        @endif
    </div>
</div>
