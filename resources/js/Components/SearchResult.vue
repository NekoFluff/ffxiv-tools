<script setup lang="ts">
defineProps<{
    text: string;
    id: number;
}>();

defineEmits(['click'])

</script>

<template>
    <button data-cy="searchResult" class="flex flex-row w-full p-3 text-sm text-white rounded-md hover:bg-blue-600"
        @click="$emit('click', id)">
        <slot name="icon"></slot>

        <span class="ml-2">
            {{ text }} (#{{ id }})
        </span>
    </button>
</template>
