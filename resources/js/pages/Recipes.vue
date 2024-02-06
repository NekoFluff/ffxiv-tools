<script setup lang="ts">
import Searchbar from '../components/searchbar.vue';
import Nav from '../components/Nav.vue';
import type { Recipe } from '../components/RecipeTree.vue';
import RecipeTree from '../components/RecipeTree.vue';
import { router } from "@inertiajs/vue3"

const props = defineProps<{
    recipe: Recipe | undefined
}>();

const handleSearch = (text) => {
    router.visit(`/${text}`)
}

</script>

<template>
    <!-- <Nav /> -->
    <div class="container mx-auto">
        <Searchbar class="mt-9" @search="handleSearch" />
        <RecipeTree v-if="props.recipe !== undefined" :recipe=props.recipe></RecipeTree>
    </div>
</template>