<?php

namespace App\Http\Clients\XIV;

interface XIVClientInterface
{
    /**
     * Fetches a recipe by its ID.
     *
     * @param int $recipeID The ID of the recipe to fetch.
     * @return array The fetched recipe.
     */
    public function fetchRecipe(int $recipeID): array;

    /**
     * Fetches the vendor cost of an item.
     *
     * @param int $item_id The ID of the item.
     * @return int The vendor cost of the item.
     */
    public function fetchVendorCost(int $item_id): int;
}
