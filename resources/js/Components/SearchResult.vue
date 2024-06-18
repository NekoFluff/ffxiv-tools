<script setup lang="ts">
import { defineProps } from "vue";
import { SearchResult } from "./SearchBar.vue";

defineProps<{
    searchResult: SearchResult;
    href?: string;
}>();

const emits = defineEmits<{
    select: [id: number, text: string];
}>();
</script>

<template>
    <Link
        v-if="href"
        :href="href"
        class="flex flex-row items-center w-full p-3 rounded-md hover:bg-blue-600"
        @click="emits('select', searchResult.id, searchResult.text)"
    >
        <img v-if="searchResult.image" class="inline w-6 h-6" :src="searchResult.image" :alt="searchResult.text" />
        <span class="ml-2 text-sm text-white"> {{ searchResult.text }} (#{{ searchResult.id }}) </span>
    </Link>

    <button
        v-else
        class="flex flex-row items-center w-full p-3 rounded-md hover:bg-blue-600"
        @click="emits('select', searchResult.id, searchResult.text)"
    >
        <img v-if="searchResult.image" class="inline w-6 h-6" :src="searchResult.image" :alt="searchResult.text" />
        <span class="ml-2 text-sm text-white"> {{ searchResult.text }} (#{{ searchResult.id }}) </span>
    </button>
</template>
