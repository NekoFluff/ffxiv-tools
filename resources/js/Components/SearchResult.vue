<script setup lang="ts">
defineProps<{
    text: string;
    id: number;
}>();
</script>

<template>
    <Link class="flex flex-row w-full p-3 text-sm text-white rounded-md hover:bg-blue-600"
        :href="route('recipe.get', { itemID: id },)">
    <slot name="icon"></slot>

    <span class="ml-2">
        {{ text }} (#{{ id }})
    </span>
    </Link>
</template>
