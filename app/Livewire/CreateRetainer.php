<?php

namespace App\Livewire;

use App\Models\Enums\Server;
use App\Models\Retainer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CreateRetainer extends Component
{
    public string $name;

    public Server $server = Server::GOBLIN;

    public function save(): void
    {
        $this->validate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->retainers()->save(new Retainer([
            'name' => $this->name,
            'server' => $this->server,
            'data_center' => $this->server->dataCenter(),
        ]));

        session()->flash('status', 'Retainer successfully updated.');

        $this->dispatch('retainer-created');

        $this->reset(['name']);
    }

    /**
     * @return array<string,mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'server' => ['required',  Rule::enum(Server::class)],
        ];
    }

    /**
     * @return array<string>
     */
    public function servers(): array
    {
        return [
            'Adamantoise',
            'Cactuar',
            'Faerie',
            'Gilgamesh',
            'Jenova',
            'Midgardsormr',
            'Sargatanas',
            'Siren',
            'Cerberus',
            'Louisoix',
            'Moogle',
            'Omega',
            'Ragnarok',
            'Spriggan',
            'Balmung',
            'Brynhildr',
            'Coerul',
            'Diabolos',
            'Goblin',
            'Malboro',
            'Mateus',
            'Zalera',
            'Aegis',
            'Atomos',
            'Carbuncle',
            'Garuda',
            'Gungnir',
            'Kujata',
            'Ramuh',
            'Tonberry',
            'Typhon',
            'Unicorn',
            'Alexander',
            'Bahamut',
            'Durandal',
            'Fenrir',
            'Ifrit',
            'Ridill',
            'Tiamat',
            'Ultima',
            'Valefor',
            'Yojimbo',
            'Zeromus',
            'Lich',
            'Odin',
            'Phoenix',
            'Shiva',
            'Zodiark',
            'Anima',
            'Asura',
            'Belias',
            'Chocobo',
            'Hades',
            'Ixion',
            'Mandragora',
            'Masamune',
            'Pandaemonium',
            'Shinryu',
            'Titan',
            'Behemoth',
            'Excalibur',
            'Exodus',
            'Famfrit',
            'Hyperion',
            'Lamia',
            'Leviathan',
            'Ultros',
        ];
    }

    public function render(): View
    {
        return view('livewire.create-retainer');
    }
}
