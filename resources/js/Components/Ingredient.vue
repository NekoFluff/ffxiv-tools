<script setup lang="ts">

import { Item } from "./Item.vue";
import type { Recipe } from "./RecipeTree.vue"

export type Ingredient = {
    item: Item,
    crafting_recipe: Recipe,
    amount: number,
    recipe_id: number,
    item_id: number,
}

</script>

<template></template>