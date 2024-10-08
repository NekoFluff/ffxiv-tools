<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <flux:button type="submit" variant="danger" class="uppercase"
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">{{ __('Delete Account') }}</flux:button>

    <x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="deleteUser" class="p-6">

            <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-6">
                <flux:input wire:model="password" :label="__('Password')" type="password" required autofocus />
            </div>

            <div class="flex justify-end mt-6">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <flux:button type="submit" variant="danger" class="uppercase ms-3">
                    {{ __('Delete Account') }}
                </flux:button>
            </div>
        </form>
    </x-modal>
</section>
