<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-4 mb-6">
          <Link :href="route('retainers')" class="text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300 text-sm">&larr; Back</Link>
          <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Edit Retainer</h1>
        </div>

        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
          <form @submit.prevent="update">
            <div class="mb-4">
              <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Name</label>
              <input v-model="form.name" type="text" maxlength="120" class="w-full border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white text-sm" />
              <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
            </div>
            <div class="mb-6">
              <label class="block text-sm text-zinc-700 dark:text-zinc-300 mb-1">Server</label>
              <select v-model="form.server" class="border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white text-sm">
                <option v-for="s in servers" :key="s" :value="s">{{ s }}</option>
              </select>
              <p v-if="form.errors.server" class="text-red-500 text-xs mt-1">{{ form.errors.server }}</p>
            </div>
            <div class="flex gap-3">
              <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-lg text-sm font-medium">Save</button>
              <Link :href="route('retainers')" class="px-4 py-2 bg-zinc-200 hover:bg-zinc-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-zinc-900 dark:text-white rounded-lg text-sm font-medium">Cancel</Link>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  retainer: Object,
  servers: Array,
});

const form = useForm({
  name: props.retainer.name,
  server: props.retainer.server,
});

function update() {
  form.put(route('retainer.update', props.retainer.id));
}
</script>
