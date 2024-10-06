<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-zinc-800 dark:text-zinc-200">
                {{ __('Retainers') }}
            </h2>
            <livewire:create-retainer />
        </div>
    </x-slot>

    <livewire:retainer-cards />
</div>
