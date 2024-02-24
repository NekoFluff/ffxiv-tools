<script setup lang="ts">
import { ref } from 'vue';

defineProps<{
    id: number,
    name: string
}>();

let showRecipeId = ref(false);
</script>

<template>
    <span class="text-sm font-medium text-gray-900" @mouseover="showRecipeId = true" @mouseleave="showRecipeId = false">
        <a :href="'https://ffxivteamcraft.com/db/en/item/' + id" target="_blank">{{ name }}</a>&nbsp;
        <span v-show="showRecipeId" class="relative">
            <span class="absolute px-2 py-1 text-sm text-white bg-gray-800 rounded-md">
                #{{ id }}
            </span>
        </span>
    </span>
</template>