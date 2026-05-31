<template>
  <GuestLayout>
    <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4">Verify Email</h2>
    <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">
      Thanks for signing up! Please verify your email by clicking the link we just sent you.
    </p>
    <div v-if="status === 'verification-link-sent'" class="mb-4 text-sm text-green-600">
      A new verification link has been sent to your email.
    </div>
    <form @submit.prevent="resend" class="mb-4">
      <button type="submit" :disabled="form.processing" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg text-sm font-medium">Resend Verification Email</button>
    </form>
    <button @click="logout" class="w-full py-2.5 bg-zinc-200 hover:bg-zinc-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-900 dark:text-white rounded-lg text-sm font-medium">Logout</button>
  </GuestLayout>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

defineProps({ status: String });

const form = useForm({});

function resend() {
  form.post(route('verification.send'));
}

function logout() {
  router.post(route('logout'));
}
</script>
