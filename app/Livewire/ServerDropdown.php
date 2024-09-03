<?php

namespace App\Livewire;

use App\Models\Enums\Server;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ServerDropdown extends Component
{
    public Server $server;

    public function mount(): void
    {
        $this->server = session('server') ?? Server::GOBLIN;
    }

    public function updateServer(string $server): void
    {
        session(['server' => Server::from($server)]);
    }

    public function render(): View
    {
        return view('livewire.server-dropdown');
    }
}
