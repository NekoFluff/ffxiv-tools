<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white mb-6">Profile</h1>

        <!-- Update profile -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 mb-6">
          <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-4">Profile Information</h2>
          <form @submit.prevent="updateProfile">
            <div class="mb-4">
              <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Name</label>
              <input v-model="profileForm.name" type="text" class="w-full border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white text-sm" />
              <p v-if="profileForm.errors.name" class="text-red-500 text-xs mt-1">{{ profileForm.errors.name }}</p>
            </div>
            <div class="mb-4">
              <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Email</label>
              <input v-model="profileForm.email" type="email" class="w-full border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white text-sm" />
              <p v-if="profileForm.errors.email" class="text-red-500 text-xs mt-1">{{ profileForm.errors.email }}</p>
            </div>
            <button type="submit" :disabled="profileForm.processing" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg text-sm font-medium">Save</button>
          </form>
        </div>

        <!-- Delete account -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-red-600 mb-4">Delete Account</h2>
          <form @submit.prevent="deleteAccount">
            <div class="mb-4">
              <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Confirm password</label>
              <input v-model="deleteForm.password" type="password" class="w-full border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white text-sm" />
              <p v-if="deleteForm.errors.password" class="text-red-500 text-xs mt-1">{{ deleteForm.errors.password }}</p>
            </div>
            <button type="submit" :disabled="deleteForm.processing" class="px-4 py-2 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white rounded-lg text-sm font-medium">Delete Account</button>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  user: Object,
});

const profileForm = useForm({
  name: props.user.name,
  email: props.user.email,
});

const deleteForm = useForm({
  password: '',
});

function updateProfile() {
  profileForm.patch(route('profile.update'));
}

function deleteAccount() {
  if (confirm('Are you sure? This cannot be undone.')) {
    deleteForm.delete(route('profile.destroy'));
  }
}
</script>
