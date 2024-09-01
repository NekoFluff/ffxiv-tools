<div>
    <h2 class="mb-2 font-bold text-center text-slate-600">Active Listings</h2>
    <table class="table w-full border border-collapse">
        <thead>
            <tr>
                <th class="px-4 py-2 bg-white border-b">Price</th>
                <th class="px-4 py-2 bg-white border-b">Amount</th>
                <th class="px-4 py-2 bg-white border-b">HQ</th>
                <th class="px-4 py-2 bg-white border-b">Retainer</th>
                <th class="px-4 py-2 bg-white border-b">Last Reviewed</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($listings as $listing)
                <tr class="border-b">
                    <td class="px-4 py-2 text-center">{{ $listing['price_per_unit'] }}</td>
                    <td class="px-4 py-2 text-center">{{ $listing['quantity'] }}</td>
                    <td class="px-4 py-2 text-center">{{ $listing['hq'] ? 'Yes' : 'No' }}</td>
                    <td class="px-4 py-2 text-center">{{ $listing['retainer_name'] }}</td>
                    <td class="px-4 py-2 text-center"
                        x-text="new Date('{{ $listing['last_review_time'] }}').toLocaleString()">
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
