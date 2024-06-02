<?php

namespace App\Http\Clients\XIV;

use Faker\Generator;

class MockXIVClient implements XIVClientInterface
{
    public const WOODEN_LOFT_RECIPE_ID = 3798;

    public function __construct(private Generator $faker)
    {
    }

    public function fetchRecipe(int $recipeID): array
    {
        if ($recipeID === self::WOODEN_LOFT_RECIPE_ID) {

            $json = file_get_contents('app\Http\Clients\XIV\fetchRecipe.json');
            $data = json_decode($json, true);
            return $data;
        }

        return [];
    }

    public function fetchVendorPrice(int $itemID): int
    {
        return $this->faker->numberBetween(1, 1000);
    }

    public function fetchItem(int $itemID): ?XIVItem
    {
        $item = new XIVItem();
        return $item->hydrate([
            'ID' => $itemID,
            'Name' => $this->faker->word,
            'Icon' => $this->faker->word,
            'Recipes' => [
                (object)[
                    'ID' => self::WOODEN_LOFT_RECIPE_ID,
                ]
            ],
        ]);
    }
}
