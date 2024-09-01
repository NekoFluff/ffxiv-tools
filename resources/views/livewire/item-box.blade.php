<section class="py-3 pl-3 pr-1 border border-dashed rounded-sm shadow-lg sm:rounded-lg border-slate-500">
    {{-- Icon and Title --}}
    <div class="flex flex-row items-center">
        <img src="{{ 'https://xivapi.com/' . $craftableItem->icon }}" class="w-6 h-6" />&nbsp;
        <a href="{{ "/v2/items/$craftableItem->item_id" }}" target="_blank">
            <span class="font-bold text-md">
                {{ $craftableItem->name }}
            </span>
        </a>
        <span class="ml-1 text-sm text-gray-700 bg-">(x{{ $craftableItem->crafting_output_count }})&nbsp;</span>

        <x-pill class="ml-2" label="Market Board Price" :value="number_format($craftableItem->market_price) . ' gil'" color="yellow" />
        @if ($craftableItem->vendor_price > 0)
            <x-pill class="ml-2" label="Vendor Price" :value="number_format($craftableItem->vendor_price) . ' gil'" color="yellow"
                href="{{ 'http://www.garlandtools.org/db/#item/' . $craftableItem->item_id }}" target="_blank" />
        @endif

        @if ($craftableItem->class_job_level > 0)
            <x-pill class="ml-2" label="Job" :value="$craftableItem->class_job" :color="$this->jobToBackgroundColor($craftableItem->class_job)" />
            <x-pill class="ml-2" label="Level" :value="$craftableItem->class_job_level" :color="$this->levelToBackgroundColor($craftableItem->class_job_level)" />
        @endif

        <x-pill class="ml-2" label="Updated" :value="Carbon\Carbon::parse($craftableItem->market_price_updated_at)->diffForHumans()" color="slate" />
    </div>

    {{-- Profit and Cost --}}
    @if ($craftableItem->optimal_craft_cost != 0)
        <div class="flex flex-row mt-2 space-x-3">
            {{-- First Column --}}
            <div class="flex-1 p-2 space-y-1 border rounded-md shadow-md">
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

            {{-- Second Column --}}
            <div class="flex flex-col justify-center flex-1 p-2 space-y-2 border rounded-md shadow-md">
                <div class="text-xs font-medium">
                    Profit if Crafted:
                    <span @class([
                        'text-green-500' => $this->profit() > 0,
                        'text-red-500' => $this->profit() < 0,
                        'text-orange-400' => $this->profit() == 0,
                    ])>
                        {{ $this->profit() }}</span>
                </div>
                <div class="text-xs font-medium">
                    Profit Ratio:
                    <span @class([
                        'text-green-500' => $this->profit() > 0,
                        'text-red-500' => $this->profit() < 0,
                        'text-orange-400' => $this->profit() == 0,
                    ])>
                        {{ $this->profitRatio() }}&nbsp;</span>
                </div>
            </div>

        </div>
    @endif

    @foreach ($craftableItem->crafting_materials as $craftingMaterial)
        <div class="mt-2 ml-5">
            <livewire:item-box :craftableItem="$craftingMaterial" />
        </div>
    @endforeach

</section>
