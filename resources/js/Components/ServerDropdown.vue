<template>
  <div>
    <label class="block text-xs text-zinc-500 dark:text-zinc-400 mb-1">Server</label>
    <select
      :value="currentServer"
      class="border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white text-sm"
      @change="onChange"
    >
      <option v-for="s in servers" :key="s" :value="s">{{ s }}</option>
    </select>
  </div>
</template>

<script setup>
import axios from 'axios';

const props = defineProps({
  servers: Array,
  currentServer: String,
});

const emit = defineEmits(['server-changed']);

async function onChange(e) {
  const server = e.target.value;
  await axios.post(route('api.server'), { server });
  emit('server-changed', server);
}
</script>
