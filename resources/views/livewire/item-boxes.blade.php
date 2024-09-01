<div>
    @if ($this->craftableItem())
        <livewire:item-box :craftableItem="$this->craftableItem()" />
    @else
        <div>
            <h2 class="text-lg ">The item <span class="font-bold">{{ $item->id }}</span> cannot be crafted.</h2>
            <p>If you believe this is an error, please contact me on discord @crazyfluff</p>
        </div>
    @endif
</div>
