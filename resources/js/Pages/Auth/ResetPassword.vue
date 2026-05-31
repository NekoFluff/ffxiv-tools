<template>
  <GuestLayout>
    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-6">Reset Password</h2>
    <form @submit.prevent="submit">
      <div class="mb-4">
        <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Email</label>
        <input v-model="form.email" type="email" autocomplete="email" class="w-full border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white text-sm" />
        <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
      </div>
      <div class="mb-4">
        <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">New Password</label>
        <input v-model="form.password" type="password" autocomplete="new-password" class="w-full border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white text-sm" />
        <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
      </div>
      <div class="mb-6">
        <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Confirm Password</label>
        <input v-model="form.password_confirmation" type="password" autocomplete="new-password" class="w-full border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white text-sm" />
      </div>
      <button type="submit" :disabled="form.processing" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg text-sm font-medium">Reset Password</button>
    </form>
  </GuestLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps({
  email: String,
  token: String,
});

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
});

function submit() {
  form.post(route('password.store'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
}
</script>
