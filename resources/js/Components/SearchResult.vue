<script setup lang="ts">
import { defineProps, inject } from "vue";

defineProps<{
    text: string;
    id: number;
}>();

const server = inject('server');

</script>

<template>
    <Link class="flex flex-row w-full p-3 text-sm text-white rounded-md hover:bg-blue-600"
        :href="route('recipe.get', { itemID: id, server: server },)">
    <slot name="icon"></slot>

    <span class="ml-2">
        {{ text }} (#{{ id }})
    </span>
    </Link>
</template>
