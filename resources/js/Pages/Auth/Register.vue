<template>
  <GuestLayout>
    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-6">Register</h2>
    <form @submit.prevent="submit">
      <div class="mb-4">
        <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Name</label>
        <input v-model="form.name" type="text" autocomplete="name" class="w-full border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white text-sm" />
        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
      </div>
      <div class="mb-4">
        <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Email</label>
        <input v-model="form.email" type="email" autocomplete="email" class="w-full border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white text-sm" />
        <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
      </div>
      <div class="mb-4">
        <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Password</label>
        <input v-model="form.password" type="password" autocomplete="new-password" class="w-full border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white text-sm" />
        <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
      </div>
      <div class="mb-6">
        <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Confirm Password</label>
        <input v-model="form.password_confirmation" type="password" autocomplete="new-password" class="w-full border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white text-sm" />
      </div>
      <button type="submit" :disabled="form.processing" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg text-sm font-medium">Register</button>
    </form>
    <p class="mt-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
      Already have an account? <Link :href="route('login')" class="text-indigo-600 hover:underline">Login</Link>
    </p>
  </GuestLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

function submit() {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
}
</script>
