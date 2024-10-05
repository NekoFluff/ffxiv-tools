<flux:autocomplete id="server" label="Server" {{ $attributes }}>
    @foreach (\App\Models\Enums\Server::all() as $server)
        <flux:autocomplete.item wire:key="{{ $server }}">{{ $server }}</flux:autocomplete.item>
    @endforeach
</flux:autocomplete>
