<x-app-layout>
    <x-slot name="title">
        {{ __('Dashboard') }}
    </x-slot>

    <x-slot name="header">
        <div class="flex items-center">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                {{ __('Search for an Item') }}
            </h2>
            <div class="flex-1 mx-7">
                <livewire:item-search-bar />
            </div>
            <livewire:server-dropdown />
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __('Check how profitable it is to craft an item using the search bar above.') }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
