<div>
    @if ($this->craftableItem())
        <livewire:item-box :craftableItem="$this->craftableItem()" />
    @else
        <div class="dark:text-zinc-200">
            <h2 class="text-lg ">It doesn't look like <span class="font-bold">{{ $item->name }}</span>
                (#{{ $item->id }}) can be crafted.</h2>
            <p class="text-sm">If you believe this is an error, feel free to contact me on discord
                <i><b>@crazyfluff</b></i>
            </p>
        </div>
    @endif
</div>
