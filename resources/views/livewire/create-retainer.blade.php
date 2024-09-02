<div x-on:retainer-created.window="function () {
        $dispatch('close-modal', 'create-retainer-modal');
}">
    <x-primary-button
        x-on:click.prevent="$dispatch('open-modal', 'create-retainer-modal')">{{ __('Create Retainer') }}</x-primary-button>

    <x-modal name="create-retainer-modal" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="createRetainer" class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Create a new retainer') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('Keep track of your retainers and the items they\'re selling. You can create a new retainer by providing a name and selecting a server.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="name" value="{{ __('Name') }}" class="sr-only" />
                <x-text-input wire:model="form.name" id="name" name="name" class="block w-3/4 mt-1"
                    placeholder="{{ __('Name') }}" />
                <x-input-error :messages="$errors->get('form.name')" class="mt-2" />
            </div>

            <div class="mt-6">
                <x-input-label for="server" value="{{ __('Server') }}" class="sr-only" />
                <x-server-dropdown class="w-3/4" wire:model="form.server" />
                <x-input-error :messages="$errors->get('form.server')" class="mt-2" />
            </div>

            <div class="flex justify-end mt-6" wire:loading.class="opacity-75 disabled">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-primary-button class="ms-3" wire:loading.class="opacity-75 disabled">
                    {{ __('Create Retainer') }}

                    <div wire:loading class="ml-3">
                        <svg class="w-5 h-5 text-black animate-spin dark:text-white" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </div>
                </x-primary-button>
            </div>
        </form>
    </x-modal>
</div>
