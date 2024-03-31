<script setup lang="ts">
import { defineProps, inject } from "vue";

defineProps<{
    text: string;
    id: number;
}>();

const emits = defineEmits(["select"]);

</script>

<template>
    <button class="flex flex-row w-full p-3 text-sm text-white rounded-md hover:bg-blue-600"
        @click="emits('select', id, text)">
        <slot name="icon"></slot>

        <span class="ml-2">
            {{ text }} (#{{ id }})
        </span>
    </button>
</template>
