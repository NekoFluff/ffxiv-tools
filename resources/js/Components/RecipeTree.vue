<script setup lang="ts">
import { defineProps, computed } from "vue";
import RecipeName from "@/Components/RecipeName.vue";
import { Recipe } from "@/types/recipe";

const props = defineProps<{
    recipe: Recipe;
}>();

const base = "https://xivapi.com/";

const profit = computed(() => {
    if (props.recipe == null) {
        return 0;
    }
    return props.recipe.item.market_price * props.recipe.amount_result - props.recipe.optimal_craft_cost;
});

const profitRatio = computed(() => {
    if (props.recipe == null) {
        return 0;
    }

    return (((props.recipe.item.market_price * props.recipe.amount_result) / props.recipe.optimal_craft_cost) * 100 - 100).toFixed(2);
});

const craftOrBuyColors = computed(() => {
    if (props.recipe == null) {
        return {};
    }

    const prices = [
        {
            value: props.recipe.purchase_cost,
            name: "Purchase Cost",
        },
        {
            value: props.recipe.market_craft_cost,
            name: "Market Craft Cost",
        },
        {
            value: props.recipe.optimal_craft_cost,
            name: "Optimal Craft Cost",
        },
    ];

    const colors = ["text-green-500", "text-orange-400", "text-red-500"];
    prices.sort((a, b) => {
        if (a.value === b.value) {
            return a.name.localeCompare(b.name);
        }
        return a.value - b.value;
    });
    let index = 0;
    let mapping: { [key: string]: string } = {};
    for (const price of prices) {
        mapping[price.name] = colors[index];
        index += 1;
    }
    return mapping;
});
</script>

<template>
    <ul v-if="props.recipe != null" class="py-4 bg-white border shadow-sm sm:rounded-lg">
        <div class="flex items-center px-3 py-1">
            <img :src="base + props.recipe.item.icon" class="w-6 h-6" />&nbsp;
            <RecipeName :id="props.recipe.item.id" :name="props.recipe.item.name" class="px-1" />
            <span class="text-sm text-gray-500">(x{{ props.recipe.amount_result }})&nbsp;</span>
            <span class="text-sm text-gray-500">{{ props.recipe.class_job }} lvl.{{ props.recipe.class_job_level }}</span>

            <span class="font-bold">&nbsp;|&nbsp;</span>
            <span class="text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                MB Price: {{ props.recipe.item.market_price }} gil</span
            >

            <span v-if="recipe.item.vendor_price != 0" class="font-bold">&nbsp;|&nbsp;</span>
            <span v-if="recipe.item.vendor_price != 0" class="text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                <a :href="'http://www.garlandtools.org/db/#item/' + recipe.item.id" target="_blank">
                    Vendor Price: {{ recipe.item.vendor_price }} gil
                </a>
            </span>

            <section class="flex flex-row items-center px-2 m-1 bg-gray-100 border border-gray-500 rounded">
                <span :class="craftOrBuyColors['Purchase Cost']" class="text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                    Purchase Cost: {{ props.recipe.purchase_cost }} gil
                    <span v-if="props.recipe.amount_result > 1"> ({{ props.recipe.purchase_cost / props.recipe.amount_result }} ea.) </span>
                </span>

                <span class="font-bold">&nbsp;|&nbsp;</span>
                <span :class="craftOrBuyColors['Market Craft Cost']" class="text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                    Market Craft Cost: {{ props.recipe.market_craft_cost }}
                    <span v-if="props.recipe.amount_result > 1"> ({{ props.recipe.market_craft_cost / props.recipe.amount_result }} ea.) </span>
                </span>

                <span class="font-bold">&nbsp;|&nbsp;</span>
                <span :class="craftOrBuyColors['Optimal Craft Cost']" class="text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                    Optimal Craft Cost: {{ props.recipe.optimal_craft_cost }} gil
                    <span v-if="props.recipe.amount_result > 1"> ({{ props.recipe.optimal_craft_cost / props.recipe.amount_result }} ea.) </span>
                </span>
            </section>

            <span
                :class="{ 'text-green-500': profit > 0, 'text-red-500': profit < 0, 'text-orange-400': profit == 0 }"
                class="text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
            >
                Profit: {{ profit }} gil</span
            >

            <span class="font-bold">&nbsp;|&nbsp;</span>
            <span
                :class="{ 'text-green-500': profit > 0, 'text-red-500': profit < 0, 'text-orange-400': profit == 0 }"
                class="text-xs font-medium tracking-wider text-left text-gray-500 uppercase"
            >
                Profit Ratio: {{ profitRatio }}%&nbsp;</span
            >
        </div>
        <li v-for="ingredient in props.recipe.ingredients" :key="ingredient.item_id" class="px-10 py-1">
            <div v-if="ingredient.crafting_recipe != null">
                <RecipeTree :recipe="ingredient.crafting_recipe" />
            </div>
            <div v-else class="flex items-center ml-3">
                <img :src="base + ingredient.item.icon" class="w-6 h-6" />&nbsp;

                <RecipeName :id="ingredient.item.id" :name="ingredient.item.name" />
                <span class="text-sm text-gray-500">(x{{ ingredient.amount }})</span>
                <span class="font-bold">&nbsp;|&nbsp;</span>
                <span class="text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                    MB Price: {{ ingredient.item.market_price }} gil</span
                >
                <span v-if="ingredient.item.vendor_price != 0" class="font-bold">&nbsp;|&nbsp;</span>
                <span v-if="ingredient.item.vendor_price != 0" class="text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                    <a :href="'http://www.garlandtools.org/db/#item/' + ingredient.item.id" target="_blank">
                        Vendor Price: {{ ingredient.item.vendor_price }} gil
                    </a>
                </span>
            </div>
        </li>
    </ul>

    <div v-else class="flex items-center justify-center h-full mt-7">
        <span class="text-lg font-medium text-gray-900">No recipes found</span>
    </div>
</template>
