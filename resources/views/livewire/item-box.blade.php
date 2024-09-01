<section
    class="py-3 pl-5 pr-1 ml-5 mr-1 bg-white border border-dashed rounded-sm shadow-lg sm:rounded-lg border-slate-500">
    {{-- Icon and Title --}}
    <div class="flex flex-row">
        <img src="{{ 'https://xivapi.com/' . $craftableItem->icon }}" class="w-6 h-6" />&nbsp;
        <a href="{{ "/v2/items/$craftableItem->item_id" }}" target="_blank">
            <span class="font-bold text-md">
                {{ $craftableItem->name }}
            </span>
        </a>
        <span class="ml-1 text-sm text-gray-500">(x{{ $craftableItem->crafting_output_count }})&nbsp;</span>
    </div>

    <div class="flex flex-row mt-2 space-x-3">
        {{-- First column --}}
        <div class="p-3 space-y-2 border rounded-md shadow-md">
            <div class="text-xs font-medium text-left text-gray-500">
                Market Board Price: <b>{{ $craftableItem->market_price }} gil</b>
                <span class=text-gray-400 x-data="{ date: new Date($wire.craftableItem.market_price_updated_at * 1000) }" x-text="'['+date.toLocaleString()+']'"></span>
            </div>

            @if ($craftableItem->vendor_price > 0)
                <div class="text-xs font-medium text-left text-gray-500">
                    <a href="{{ 'http://www.garlandtools.org/db/#item/' . $craftableItem->item_id }}" target="_blank">
                        Vendor Price: {{ $craftableItem->vendor_price }} gil
                    </a>
                </div>
            @endif

            @if ($craftableItem->class_job_level > 0)
                <div class="text-xs font-medium text-left text-gray-400">Req.
                    {{ $craftableItem->class_job }}
                    lvl.{{ $craftableItem->class_job_level }}</div>
            @endif
        </div>

        @if ($craftableItem->optimal_craft_cost != 0)
            {{-- Second Column --}}
            <div class="p-3 space-y-2 border rounded-md shadow-md">
                <div @class([
                    'text-xs font-medium',
                    $this->craftOrBuyColors()['Purchase Cost'],
                ])>
                    Purchase Cost: {{ $craftableItem->purchase_cost }} gil
                    @if ($craftableItem->crafting_output_count > 1)
                        <span>({{ $craftableItem->purchase_cost / $craftableItem->crafting_output_count }} ea.) </span>
                    @endif
                </div>

                <div @class([
                    'text-xs font-medium',
                    $this->craftOrBuyColors()['Market Craft Cost'],
                ])>
                    Market Craft Cost: {{ $craftableItem->market_craft_cost }} gil
                    @if ($craftableItem->crafting_output_count > 1)
                        <span>({{ $craftableItem->market_craft_cost / $craftableItem->crafting_output_count }} ea.)
                        </span>
                    @endif

                </div>

                <div @class([
                    'text-xs font-medium',
                    $this->craftOrBuyColors()['Optimal Craft Cost'],
                ])>
                    Optimal Craft Cost: {{ $craftableItem->optimal_craft_cost }} gil
                    @if ($craftableItem->crafting_output_count > 1)
                        <span>({{ $craftableItem->optimal_craft_cost / $craftableItem->crafting_output_count }} ea.)
                        </span>
                    @endif
                </div>
            </div>

            {{-- Third Column --}}
            <div class="p-3 space-y-2 border rounded-md shadow-md">
                <div @class([
                    'text-xs font-medium',
                    'text-green-500' => $this->profit() > 0,
                    'text-red-500' => $this->profit() < 0,
                    'text-orange-400' => $this->profit() == 0,
                ])>
                    Profit: {{ $this->profit() }} gil</div>

                <div @class([
                    'text-xs font-medium',
                    'text-green-500' => $this->profit() > 0,
                    'text-red-500' => $this->profit() < 0,
                    'text-orange-400' => $this->profit() == 0,
                ])>
                    Profit Ratio: {{ $this->profitRatio() }}&nbsp;</div>
            </div>
        @endif
    </div>

    @foreach ($craftableItem->crafting_materials as $craftingMaterial)
        <div class="mt-3 ml-4">
            <livewire:item-box :craftableItem="$craftingMaterial" />
        </div>
    @endforeach

</section>
