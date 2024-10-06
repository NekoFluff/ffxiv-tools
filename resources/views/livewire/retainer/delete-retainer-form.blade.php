<?php

use App\Models\Retainer;
use Livewire\Volt\Component;

new class extends Component {
    public Retainer $retainer;

    public string $name = '';

    public function mount(Retainer $retainer): void
    {
        $this->retainer = $retainer;
    }

    /**
     * Delete the retainer.
     */
    public function deleteRetainer(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'in:' . $this->retainer->name],
        ]);

        $this->authorize('delete', $this->retainer);

        $this->retainer->delete();

        $this->redirect('/retainers', navigate: true);
    }
}; ?>

<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">
            {{ __('Delete Retainer') }}
        </h2>

        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Once your retainer is deleted, all of its resources and data will be permanently deleted.') }}
        </p>
    </header>

    <flux:button type="submit" variant="danger" class="uppercase"
        x-on:click.prevent="$dispatch('open-modal', 'confirm-retainer-deletion')">
        {{ __('Delete Retainer') }}
    </flux:button>

    <x-modal name="confirm-retainer-deletion" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="deleteRetainer" class="p-6">

            <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">
                {{ __('Are you sure you want to delete your retainer?') }}
            </h2>

            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Once your retainer is deleted, all of its resources and data will be permanently deleted. Please enter the name of the retainer to confirm you would like to permanently delete their information.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="name" value="{{ __('Name') }}" class="sr-only" />

                <x-text-input wire:model="name" id="name" name="name" class="block w-3/4 mt-1"
                    placeholder="{{ __('Name') }}" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div class="flex justify-end mt-6">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <flux:button type="submit" variant="danger" class="uppercase ms-3">
                    {{ __('Delete Retainer') }}
                </flux:button>
            </div>
        </form>
    </x-modal>
</section>
