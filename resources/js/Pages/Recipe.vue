<script setup lang="ts">
import SearchBar from '@/Components/SearchBar.vue';
import type { Recipe } from '@/Components/RecipeTree.vue';
import RecipeTree from '@/Components/RecipeTree.vue';
import ListingsTable from '@/Components/ListingsTable.vue';
import PriceHistoryGraph from '@/Components/PriceHistoryGraph.vue';
import { Item } from '@/Components/Item.vue';

const props = defineProps<{
    item: Item | undefined
    recipe: Recipe | undefined
    history: Array<{}>
    listings: Array<{}>
    lastUpdated: string
}>();


const totalSold = props.history != null ? props.history.reduce((acc, item) => acc + item['quantity'], 0) : 0
const totalListed = props.listings != null ? props.listings.reduce((acc, item) => acc + item['quantity'], 0) : 0

</script>

<template>
    <!-- <Nav /> -->

    <Head :title="props.item?.name || 'Recipe Search'" />
    <div class="container mx-auto">
        <SearchBar class="pt-9" />
        <div v-if="lastUpdated" class="flex justify-end text-sm">
            <h2 class="mr-2 text-sm text-gray-500">Last Updated: {{ lastUpdated }}</h2>
            (
            <Link class="text-sm" :href="route('recipe.get', { id: item.id })" :data="{ recalculate: 1 }">
            Refresh</Link>
            )
        </div>
        <RecipeTree v-if="recipe" :recipe="recipe" />
        <h1 v-if="!recipe && item" class="flex justify-center my-10 text-lg">There is no recipe for&nbsp;<span
                class="font-bold"> {{
                    item.name }}</span>.</h1>
        <div v-if="recipe" class="p-2 mt-2 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">#
            sold in The last 7
            days:<span class="ml-4 text-base font-bold">{{ totalSold }}</span></div>
        <div v-if="recipe" class="p-2 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">#
            Currently on Market Board:<span class="ml-4 text-base font-bold">{{ totalListed }}</span></div>
        <div class="grid grid-cols-10 gap-4 mt-6 mb-10">
            <div class="col-span-6">
                <PriceHistoryGraph v-if="history" :history="history" />
            </div>
            <div class="col-span-4">
                <ListingsTable v-if="listings" class="mt-4 mb-5 bg-gray-50" :listings="listings" />
            </div>
        </div>
    </div>
</template>