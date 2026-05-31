<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
          <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Retainers</h1>
          <button @click="showCreateForm = !showCreateForm" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">
            Add Retainer
          </button>
        </div>

        <!-- Create form -->
        <div v-if="showCreateForm" class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 mb-6">
          <h2 class="text-lg font-semibold text-zinc-800 dark:text-zinc-200 mb-4">Add Retainer</h2>
          <form @submit.prevent="createRetainer">
            <div class="flex gap-4">
              <div class="flex-1">
                <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Name</label>
                <input v-model="form.name" type="text" maxlength="120" class="w-full border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white text-sm" />
                <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
              </div>
              <div>
                <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Server</label>
                <select v-model="form.server" class="border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white text-sm">
                  <option v-for="s in servers" :key="s" :value="s">{{ s }}</option>
                </select>
              </div>
              <div class="flex items-end">
                <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg text-sm font-medium">Save</button>
              </div>
            </div>
          </form>
        </div>

        <!-- Retainer cards -->
        <div v-if="retainers.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="retainer in retainers" :key="retainer.id" class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
            <div class="flex items-start justify-between">
              <div>
                <h3 class="font-semibold text-zinc-900 dark:text-white">{{ retainer.name }}</h3>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ retainer.server }}</p>
              </div>
              <div class="flex gap-2">
                <Link :href="route('retainer.edit', retainer.id)" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Edit</Link>
                <button @click="deleteRetainer(retainer)" class="text-xs text-red-500 hover:underline">Delete</button>
              </div>
            </div>
            <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-2">{{ retainer.listings?.length ?? 0 }} active listings</p>
          </div>
        </div>

        <div v-else class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 text-zinc-500 dark:text-zinc-400">
          No retainers yet. Add one to get started.
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  retainers: Array,
  servers: Array,
  currentServer: String,
});

const showCreateForm = ref(false);

const form = useForm({
  name: '',
  server: props.currentServer ?? 'Goblin',
});

function createRetainer() {
  form.post(route('retainer.store'), {
    onSuccess: () => {
      form.reset();
      showCreateForm.value = false;
    },
  });
}

function deleteRetainer(retainer) {
  if (confirm(`Delete retainer "${retainer.name}"?`)) {
    router.delete(route('retainer.destroy', retainer.id));
  }
}
</script>
