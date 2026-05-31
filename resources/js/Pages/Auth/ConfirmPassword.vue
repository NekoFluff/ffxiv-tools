<template>
  <GuestLayout>
    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4">Confirm Password</h2>
    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">Please confirm your password before continuing.</p>
    <form @submit.prevent="submit">
      <div class="mb-4">
        <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Password</label>
        <input v-model="form.password" type="password" autocomplete="current-password" class="w-full border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white text-sm" />
        <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
      </div>
      <button type="submit" :disabled="form.processing" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg text-sm font-medium">Confirm</button>
    </form>
  </GuestLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const form = useForm({ password: '' });

function submit() {
  form.post(route('password.confirm'), {
    onFinish: () => form.reset('password'),
  });
}
</script>
