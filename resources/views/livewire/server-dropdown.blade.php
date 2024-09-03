<select id="server-dropdown"
    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-500"
    wire:model="server"
    x-on:change="function () {
        $dispatch('server-changed', { server: $event.target.value });
        $wire.updateServer($event.target.value);
    }">

    @foreach (\App\Models\Enums\Server::all() as $server)
        <option wire:key="{{ $server }}" value="{{ $server }}">{{ $server }}
        </option>
    @endforeach
</select>
