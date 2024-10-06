<?php

use App\Livewire\Forms\RetainerForm;
use App\Models\Retainer;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Volt\Component;

new class extends Component {
    public Retainer $retainer;

    public RetainerForm $form;

    /**
     * Mount the component.
     */
    public function mount($retainer): void
    {
        $this->retainer = $retainer;

        $this->form->name = $retainer->name;
        $this->form->server = $retainer->server->value;
    }

    /**
     * Update the retainer information for the currently authenticated user.
     */
    public function updateRetainerInformation(): void
    {
        $this->authorize('update', $this->retainer);

        $this->form->update($this->retainer);

        // TODO: Add a success message.
        $this->dispatch('retainer-updated');

        Session::flash('status', 'retainer-updated');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">
            {{ __('Retainer Information') }}
        </h2>

        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            {{ __("Update your retainer's name and server.") }}
        </p>
    </header>

    <form wire:submit="updateRetainerInformation" class="mt-6 space-y-4">
        <div>
            <flux:input wire:model="form.name" :label="__('Name')" type="text" required autofocus />
        </div>

        <div>
            <x-server-dropdown class="w-full" wire:model="form.server" />
        </div>

        <div class="flex items-center gap-4">
            <flux:button type="submit" variant="primary" class="uppercase">{{ __('Save') }}</flux:button>

            <x-action-message class="me-3" on="retainer-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
