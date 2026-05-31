<template>
  <GuestLayout>
    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4">Forgot Password</h2>
    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">Enter your email and we'll send you a password reset link.</p>
    <div v-if="status" class="mb-4 text-sm text-green-600">{{ status }}</div>
    <form @submit.prevent="submit">
      <div class="mb-4">
        <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Email</label>
        <input v-model="form.email" type="email" autocomplete="email" class="w-full border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white text-sm" />
        <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
      </div>
      <button type="submit" :disabled="form.processing" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg text-sm font-medium">Send Reset Link</button>
    </form>
  </GuestLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

defineProps({ status: String });

const form = useForm({ email: '' });

function submit() {
  form.post(route('password.email'));
}
</script>
