<?php

use App\Livewire\Forms\RetainerForm;
use App\Models\Retainer;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Livewire\Volt\Component;

new class extends Component
{
    public Retainer $retainer;

    public RetainerForm $form;

    /**
     * Mount the component.
     */
    public function mount($retainer): void
    {
        $this->retainer = $retainer;

        $this->form->name = $retainer->name;
        $this->form->server = $retainer->server;
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
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Retainer Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your retainer's name and server.") }}
        </p>
    </header>

    <form wire:submit="updateRetainerInformation" class="mt-6 space-y-6">
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="form.name" id="name" name="name" type="text" class="block w-full mt-1"
                required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="server" value="{{ __('Server') }}" />
            <x-server-dropdown class="w-full" wire:model="form.server" />
            <x-input-error :messages="$errors->get('server')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="retainer-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
