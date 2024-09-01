@once
    {{-- TODO: I installed ChartJS so I shouldn't need to pull it from CDN. Not sure how to do it yet --}}
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush
@endonce

<div>
    <div class="mx-auto mt-5 max-w-7xl sm:px-6 lg:px-8">
        @if ($item)
            <div class="mb-10">
                <livewire:item-boxes :$item />
            </div>
            <div class="flex mb-10">
                <div class="flex-1">
                    {{-- TODO: Pass through server --}}
                    <livewire:quantity-sold-chart :itemID="$item->id" server="Goblin" />
                </div>
                <div class="flex-1">
                    {{-- TODO: Pass through server --}}
                    <livewire:price-history-chart :itemID="$item->id" server="Goblin" />
                </div>
            </div>
            <div class="mb-20">
                {{-- TODO: Pass through server --}}
                <livewire:current-listings-table :itemID="$item->id" server="Goblin" />
            </div>
        @else
            <div>
                <h2 class="text-lg ">The item <span class="font-bold">{{ $id }}</span> doesn't seem to exist in
                    our
                    system.</h2>
                <p>We'll try to fetch the item data for you. If there is an actual item associated with the ID you
                    provided,
                    you should be able to refresh the page in a minute and additional data will appear. Otherwise check
                    to
                    make sure you've input the correct item ID.</p>
            </div>
        @endif
    </div>
</div>
