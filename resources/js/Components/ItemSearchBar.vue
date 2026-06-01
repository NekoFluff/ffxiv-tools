<template>
  <div class="relative">
    <input
      v-model="search"
      type="text"
      placeholder="Search items..."
      class="w-full border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white text-sm"
      @input="onInput"
      @blur="setTimeout(() => showResults = false, 150)"
      @focus="showResults = items.length > 0"
    />
    <ul v-if="showResults && items.length" class="absolute z-10 w-full mt-1 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-lg max-h-64 overflow-y-auto">
      <li
        v-for="item in items"
        :key="item.id"
        class="px-3 py-2 hover:bg-zinc-100 dark:hover:bg-zinc-700 cursor-pointer text-sm text-zinc-900 dark:text-zinc-100"
        @mousedown="goToItem(item)"
      >
        <img v-if="item.icon" :src="'https://v2.xivapi.com/api/asset?format=png&path=' + item.icon" class="w-5 h-5 rounded inline-block mr-1" />
        {{ item.name }}
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const search = ref('');
const items = ref([]);
const showResults = ref(false);

let debounce = null;

function onInput() {
  clearTimeout(debounce);
  if (!search.value.trim()) {
    items.value = [];
    showResults.value = false;
    return;
  }
  debounce = setTimeout(async () => {
    const { data } = await axios.get(route('api.items.search'), { params: { q: search.value } });
    items.value = data;
    showResults.value = data.length > 0;
  }, 300);
}

function goToItem(item) {
  showResults.value = false;
  router.get(route('item.show', item.id));
}
</script>
