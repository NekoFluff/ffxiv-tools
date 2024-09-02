<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                {{ __('Retainers') }}
            </h2>
            <livewire:create-retainer />
        </div>
    </x-slot>

    <livewire:retainer-cards />
</div>
