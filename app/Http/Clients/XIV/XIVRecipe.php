<?php

namespace App\Http\Clients\XIV;

use Illuminate\Support\Collection;

class XIVRecipe
{
    public int $ID;

    public XIVItem $ResultItem;

    public int $AmountResult;

    public string $CraftType;

    public string $ClassJobName;

    public int $ClassJobLevel;

    /** @var Collection<int, XIVIngredient> */
    public Collection $Ingredients;

    /**
     * @param array<string,mixed> $data
     * @return Collection<int, XIVRecipe>
     */
    public static function hydrateFromRecipeSearchResponse(array $data): Collection
    {
        $recipes = new Collection();
        foreach ($data['results'] as $recipeData) {
            $recipes->push(self::hydrateFromRecipeFetchResponse($recipeData));
        }
        return $recipes;
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function hydrateFromRecipeFetchResponse(array $data): self
    {
        $recipe = new self();
        $recipe->ID = $data['row_id'];
        $fields = $data['fields'] ?? [];
        $recipe->ResultItem = new XIVItem(
            $fields['ItemResult']['row_id'],
            $fields['ItemResult']['fields']['Name'],
            $fields['ItemResult']['fields']['Icon']['path']
        );
        $recipe->AmountResult = $fields['AmountResult'];
        $recipe->CraftType = $fields['CraftType']['fields']['Name'];
        $recipe->Ingredients = new Collection();
        $recipe->ClassJobName = $fields['ItemResult']['fields']['ClassJobRepair']['fields']['NameEnglish'];
        $recipe->ClassJobLevel = $fields['RecipeLevelTable']['fields']['ClassJobLevel'];

        foreach ($fields['Ingredient'] as $i => $ingredient) {
            if ($fields['AmountIngredient'][$i] == 0) {
                continue;
            }

            $recipe->Ingredients->push(new XIVIngredient(
                $ingredient['row_id'],
                $ingredient['fields']['Name'],
                $ingredient['fields']['Icon']['path'],
                $fields['AmountIngredient'][$i]
            ));
        }

        return $recipe;
    }
}
