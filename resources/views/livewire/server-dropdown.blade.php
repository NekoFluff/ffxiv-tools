<flux:dropdown id="server-dropdown">
    <flux:button icon-trailing="chevron-down">{{ $server }}</flux:button>

    <flux:menu class="max-h-96">
        @foreach (\App\Models\Enums\Server::all() as $server)
            <flux:menu.item wire:key="{{ $server }}"
                x-on:click="function () {
                    $dispatch('server-changed', { server: '{{ $server }}'});
                    $wire.updateServer('{{ $server }}');
                }">
                {{ $server }}
            </flux:menu.item>
        @endforeach
    </flux:menu>
</flux:dropdown>
