<script setup lang="ts">

import type { Recipe } from "./RecipeTree.vue"

export type Ingredient = {
    item_id: number,
    name: string,
    amount: number,
    market_cost: number,
    vendor_cost: number,
    icon: string,
    recipe: Recipe | null,
}

</script>

<template></template>