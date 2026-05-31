<template>
  <AppLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-8">
          <h2 class="text-xl font-semibold text-zinc-800 dark:text-zinc-200">Market Board</h2>
          <ServerDropdown :servers="servers" :current-server="currentServer" />
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 text-zinc-900 dark:text-zinc-100">
          Use the search bar in the top navigation to find an item and check its market board data and crafting profitability.
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ServerDropdown from '@/Components/ServerDropdown.vue';

const props = defineProps({
  servers: Array,
  currentServer: String,
});
</script>
