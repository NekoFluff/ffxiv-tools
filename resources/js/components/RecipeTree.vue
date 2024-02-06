<script setup lang="ts">
import type { Ingredient } from "./Ingredient.vue"
import { defineProps, computed } from 'vue';
import RecipeName from "./RecipeName.vue";

export type Recipe = {
    item_id: number,
    name: string,
    icon: string,
    ingredients: Ingredient[],
    amount_result: number,
    market_cost: number,
    market_craft_cost: number,
    optimal_craft_cost: number,
    vendor_cost: number,
    class_job: string,
    class_job_icon: string,
}

const props = defineProps<{
    recipe: Recipe
}>();

const base = "https://xivapi.com/";

const profit = computed(() => {
    if (props.recipe == null) {
        return 0;
    }
    return props.recipe.market_cost - props.recipe.optimal_craft_cost;
})

const profitRatio = computed(() => {
    if (props.recipe == null) {
        return 0;
    }

    return ((props.recipe.market_cost / props.recipe.optimal_craft_cost * 100) - 100).toFixed(2);
})

const craftOrBuyColors = computed(() => {
    if (props.recipe == null) {
        return "";
    }

    const prices = [
        {
            value: props.recipe.market_cost,
            name: "Market Price"
        },
        {
            value: props.recipe.market_craft_cost,
            name: "Market Craft Cost"
        },
        {
            value: props.recipe.optimal_craft_cost,
            name: "Optimal Craft Cost"
        }
    ]

    const colors = ["text-green-500", "text-yellow-500", "text-red-500"]
    prices.sort((a, b) => {
        if (a.value === b.value) {
            return a.name.localeCompare(b.name);
        }
        return a.value - b.value;
    });
    let index = 0;
    let mapping = [];
    for (const price of prices) {
        mapping[price.name] = colors[index];
        index += 1
    }
    return mapping;
})

</script>

<template>
    <ul v-if="props.recipe != null" class="py-2 border border-gray-300 rounded-lg bg-gray-50">
        <div class="flex items-center px-3 py-1">
            <img :src="base + props.recipe.icon" class="w-6 h-6" />&nbsp;
            <RecipeName :id="props.recipe.item_id" :name="props.recipe.name" />
            <span class="text-sm text-gray-500">(x{{ props.recipe.amount_result }})&nbsp;</span>
            <span class="text-sm text-gray-500">{{ props.recipe.class_job }}</span>
            <span class="font-bold">&nbsp;|&nbsp;</span>

            <span :class="craftOrBuyColors['Market Price']"
                class="text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                MB Price: {{ props.recipe.market_cost }} gil</span>
            <span class="font-bold">&nbsp;|&nbsp;</span>
            <span v-if="recipe.vendor_cost != 0"
                class="text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                <a :href="'http://www.garlandtools.org/db/#item/' + recipe.item_id" target="_blank">
                    Vendor Price: {{ recipe.vendor_cost }} gil
                </a>
            </span>
            <span v-if="recipe.vendor_cost != 0" class="font-bold">&nbsp;|&nbsp;</span>
            <span :class="craftOrBuyColors['Market Craft Cost']"
                class="text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                Market Craft Cost: {{ props.recipe.market_craft_cost }} gil</span>
            <span class="font-bold">&nbsp;|&nbsp;</span>
            <span :class="craftOrBuyColors['Optimal Craft Cost']"
                class="text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                Optimal Craft Cost: {{ props.recipe.optimal_craft_cost }} gil</span>
            <span class="font-bold">&nbsp;|&nbsp;</span>
            <span :class="{ 'text-green-500': profit > 0, 'text-red-500': profit < 0, 'text-yellow-500': profit == 0 }"
                class="text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                Profit: {{ profit }} gil</span>
            <span class="font-bold">&nbsp;|&nbsp;</span>
            <span :class="{ 'text-green-500': profit > 0, 'text-red-500': profit < 0, 'text-yellow-500': profit == 0 }"
                class="text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                Profit Ratio: {{ profitRatio }}%&nbsp;</span>

        </div>
        <li v-for=" ingredient  in  props.recipe.ingredients " :key="ingredient.item_id" class="px-10 py-1">
            <div v-if="ingredient.recipe != null">
                <RecipeTree :recipe="ingredient.recipe" />
            </div>
            <div v-else class="flex items-center ml-3">
                <img :src="base + ingredient.icon" class="w-6 h-6" />&nbsp;

                <RecipeName :id="ingredient.item_id" :name="ingredient.name" />
                <span class="text-sm text-gray-500">(x{{ ingredient.amount }})</span>
                <span class="font-bold">&nbsp;|&nbsp;</span>
                <span class="text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                    MB Price: {{ ingredient.market_cost }} gil</span>
                <span v-if="ingredient.vendor_cost != 0" class="font-bold">&nbsp;|&nbsp;</span>
                <span v-if="ingredient.vendor_cost != 0"
                    class="text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                    <a :href="'http://www.garlandtools.org/db/#item/' + ingredient.item_id" target="_blank">
                        Vendor Price: {{ ingredient.vendor_cost }} gil
                    </a>
                </span>
            </div>
        </li>
    </ul>
    <div v-else class="flex items-center justify-center h-full mt-7">
        <span class="text-lg font-medium text-gray-900">No recipes found</span>

    </div>
</template>