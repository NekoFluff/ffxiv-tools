<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    id: number,
    name: string
}>();

let showRecipeId = ref(false);
</script>

<template>
    <span class="text-sm font-medium text-gray-900" @mouseover="showRecipeId = true" @mouseleave="showRecipeId = false">
        <Link :href="route('recipe.get', { id: id })">{{ name }}&nbsp;</Link>
        <div v-show="showRecipeId" class="relative">
            <div class="absolute px-2 py-1 text-sm text-white bg-gray-800 rounded-md">
                #{{ id }}
                <div>
                    <a :href="'http://www.garlandtools.org/db/#item/' + id" target="_blank">garlandtools</a>
                </div>
            </div>
        </div>
    </span>
</template>