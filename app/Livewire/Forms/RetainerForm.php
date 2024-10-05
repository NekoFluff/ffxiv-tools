<?php

namespace App\Livewire\Forms;

use App\Models\Enums\Server;
use App\Models\Retainer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Form;

class RetainerForm extends Form
{
    public string $name = '';

    public string $server;

    public function mount(): void
    {
        $this->server = session('server') ?? Server::GOBLIN;
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

    public function update(Retainer $retainer): void
    {
        $this->validate();

        $retainer->fill([
            'name' => $this->name,
            'server' => $this->server,
            'data_center' => Server::from($this->server)->dataCenter(),
        ]);

        $retainer->save();
    }

    public function store(): bool
    {
        $this->validate();

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->retainers()->count() >= 10) {
            $this->addError('name', 'You have reached the maximum number of 10 retainers.');

            return false;
        }

        if ($user->retainers()->where('name', $this->name)->where('server', $this->server)->exists()) {
            $this->addError('name', 'You already have a retainer with that name.');

            return false;
        }

        $user->retainers()->save(new Retainer([
            'name' => $this->name,
            'server' => $this->server,
            'data_center' => Server::from($this->server)->dataCenter(),
        ]));

        // TODO: Remove
        session()->flash('status', 'Retainer successfully updated.');

        $this->reset(['name']);

        return true;
    }
}
