<script setup lang="ts">
import Searchbar from '../components/searchbar.vue';
import Nav from '../components/Nav.vue';
import type { Recipe } from '../components/RecipeTree.vue';
import RecipeTree from '../components/RecipeTree.vue';
import ListingsTable from '../components/ListingsTable.vue';
import PriceHistoryGraph from '../components/PriceHistoryGraph.vue';
import { router } from "@inertiajs/vue3"

const props = defineProps<{
    recipe: Recipe | undefined
    history: Array<{}>
    listings: Array<{}>
}>();

const handleSearch = (text) => {
    router.visit(`/${text}`)
}

const totalSold = props.history.reduce((acc, item) => acc + item['quantity'], 0)

</script>

<template>
    <!-- <Nav /> -->
    <div class="container mx-auto">
        <Searchbar class="pt-9" @search="handleSearch" />
        <RecipeTree v-if="props.recipe !== undefined" :recipe="props.recipe"></RecipeTree>
        <div class="p-2 mt-2 text-xs font-medium tracking-wider text-left text-gray-500 uppercase"># sold in The last 7
            days:<span class="ml-4 text-base font-bold">{{ totalSold }}</span></div>
        <div class="grid grid-cols-9 gap-4 mt-6 mb-10">
            <div class="col-span-7">
                <PriceHistoryGraph :history="props.history"></PriceHistoryGraph>
            </div>
            <div class="col-span-2">
                <ListingsTable class="mt-4 mb-5 bg-gray-50" :listings="props.listings"></ListingsTable>
            </div>
        </div>
    </div>
</template>