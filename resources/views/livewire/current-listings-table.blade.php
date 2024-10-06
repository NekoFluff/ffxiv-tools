<div class="text-zinc-600 dark:text-zinc-200">
    <h2 class="mb-2 font-bold text-center ">Active Listings</h2>
    <table class="table w-full border border-collapse">
        <thead class="text-zinc-800">
            <tr>
                <th class="px-4 py-2 bg-white border-b">Price</th>
                <th class="px-4 py-2 bg-white border-b">Amount</th>
                <th class="px-4 py-2 bg-white border-b">HQ</th>
                <th class="px-4 py-2 bg-white border-b">Retainer</th>
                <th class="px-4 py-2 bg-white border-b">Last Reviewed</th>
            </tr>
        </thead>
        <tbody>
            @if (empty($listings))
                <tr>
                    <td class="px-4 py-2 text-center dark:text-white" colspan="5">No listings found.</td>
                </tr>
            @endif

            @foreach ($listings as $listing)
                <tr wire:key="{{ $listing['id'] }}" class="border-b border-gray-600 dark:border-gray-200">
                    <td class="px-4 py-2 text-center dark:text-white">{{ $listing['price_per_unit'] }}</td>
                    <td class="px-4 py-2 text-center dark:text-white">{{ $listing['quantity'] }}</td>
                    <td class="px-4 py-2 text-center dark:text-white">{{ $listing['hq'] ? 'Yes' : 'No' }}</td>
                    <td class="px-4 py-2 text-center dark:text-white">{{ $listing['retainer_name'] }}</td>
                    <td class="px-4 py-2 text-center dark:text-white"
                        x-text="new Date('{{ $listing['last_review_time'] }}').toLocaleString()">
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
