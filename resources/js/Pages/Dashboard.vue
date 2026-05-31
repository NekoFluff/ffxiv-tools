<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-4 mb-8">
          <h2 class="text-xl font-semibold text-zinc-800 dark:text-zinc-200">Search for an Item</h2>
          <div class="flex-1">
            <ItemSearchBar />
          </div>
          <ServerDropdown :servers="servers" :current-server="currentServer" />
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 text-zinc-900 dark:text-zinc-100">
          Check how profitable it is to craft an item using the search bar above.
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ItemSearchBar from '@/Components/ItemSearchBar.vue';
import ServerDropdown from '@/Components/ServerDropdown.vue';
import { usePage } from '@inertiajs/vue3';

const { props } = usePage();
const servers = props.servers ?? [];
const currentServer = props.currentServer ?? 'Goblin';
</script>
