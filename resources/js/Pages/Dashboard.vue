<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SearchBar from '@/Components/SearchBar.vue';
import RecipeTree from '@/Components/RecipeTree.vue';
import ListingsTable from '@/Components/ListingsTable.vue';
import PriceHistoryGraph from '@/Components/PriceHistoryGraph.vue';
import { Listing } from '@/types/listing';
import { Item } from '@/types/item';
import { Recipe } from '@/types/recipe';

const props = defineProps<{
    item: Item | undefined
    recipe: Recipe | undefined
    history: Array<{}>
    listings: Array<Listing>
    lastUpdated: string
}>();

const totalSold = props.history != null ? props.history.reduce((acc, item: any) => acc + item['quantity'], 0) : 0
const totalListed = props.listings != null ? props.listings.reduce((acc, item: any) => acc + item['quantity'], 0) : 0

</script>

<template>
    <!-- <Head title="Dashboard" /> -->

    <Head :title="item?.name || 'Dashboard'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="mr-10 text-xl font-semibold leading-tight text-gray-800">{{ item?.name || 'Item Search' }}</h2>
                <SearchBar class="flex-grow" />
            </div>
        </template>



        <div class="py-10">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">You're logged in!</div>
                </div> -->


                <div v-if="lastUpdated" class="flex justify-end text-sm">
                    <h2 class="mr-2 text-sm text-gray-500">Last Updated: {{ lastUpdated }}</h2>
                    (
                    <Link class="text-sm" :href="route('recipe.get', { id: item?.id })" :data="{ recalculate: 1 }">
                    Refresh
                    </Link>
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
                <div v-if="item" class="grid grid-cols-10 gap-4 mt-10 mb-10">
                    <div class="col-span-6">
                        <PriceHistoryGraph v-if="history" :history="history" />
                    </div>
                    <div class="col-span-4">
                        <ListingsTable v-if="listings" class="mt-4 mb-5 bg-gray-50" :listings="listings" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
