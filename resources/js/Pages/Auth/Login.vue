<template>
  <GuestLayout>
    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-6">Login</h2>
    <div v-if="status" class="mb-4 text-sm text-green-600">{{ status }}</div>
    <form @submit.prevent="submit">
      <div class="mb-4">
        <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Email</label>
        <input v-model="form.email" type="email" autocomplete="email" class="w-full border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white text-sm" />
        <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
      </div>
      <div class="mb-4">
        <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Password</label>
        <input v-model="form.password" type="password" autocomplete="current-password" class="w-full border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white text-sm" />
        <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
      </div>
      <div class="flex items-center justify-between mb-6">
        <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
          <input v-model="form.remember" type="checkbox" class="rounded border-zinc-300" />
          Remember me
        </label>
        <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm text-indigo-600 hover:underline">Forgot password?</Link>
      </div>
      <button type="submit" :disabled="form.processing" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg text-sm font-medium">Login</button>
    </form>
    <p class="mt-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
      No account? <Link :href="route('register')" class="text-indigo-600 hover:underline">Register</Link>
    </p>
  </GuestLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

defineProps({
  canResetPassword: Boolean,
  status: String,
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

function submit() {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
}
</script>
