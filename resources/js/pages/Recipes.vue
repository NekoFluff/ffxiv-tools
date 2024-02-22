<script setup lang="ts">
import Searchbar from '../components/searchbar.vue';
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

const totalSold = props.history != null ? props.history.reduce((acc, item) => acc + item['quantity'], 0) : 0
const totalListed = props.listings != null ? props.listings.reduce((acc, item) => acc + item['quantity'], 0) : 0
</script>

<template>
    <!-- <Nav /> -->
    <div class="container mx-auto">
        <Searchbar class="pt-9" @search="handleSearch" />
        <RecipeTree v-if="props.recipe" :recipe="props.recipe" />
        <h1 v-else class="flex justify-center my-10 text-lg">There is no recipe.</h1>
        <div v-if="props.recipe" class="p-2 mt-2 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">#
            sold in The last 7
            days:<span class="ml-4 text-base font-bold">{{ totalSold }}</span></div>
        <div v-if="props.recipe" class="p-2 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">#
            Currently on Market Board:<span class="ml-4 text-base font-bold">{{ totalListed }}</span></div>
        <div class="grid grid-cols-10 gap-4 mt-6 mb-10">
            <div class="col-span-6">
                <PriceHistoryGraph v-if="props.history" :history="props.history" />
            </div>
            <div class="col-span-4">
                <ListingsTable v-if="props.listings" class="mt-4 mb-5 bg-gray-50" :listings="props.listings" />
            </div>
        </div>
    </div>
</template>