<?php

namespace App\Http\Clients\XIV;

use Faker\Generator;

class MockXIVClient implements XIVClientInterface
{
    public const WOODEN_LOFT_RECIPE_ID = 3798;
    public const WOODEN_LOFT_ITEM_ID = 24511;
    public const ROSEWOOD_LUMBER_RECIPE_ID = 1092;
    public const ROSEWOOD_LUMBER_ITEM_ID = 5376;
    public const MYTHRIL_RIVETS_RECIPE_ID = 139;
    public const MYTHRIL_RIVETS_ITEM_ID = 5099;
    public const MYTHRIL_INGOT_RECIPE_ID = 135;
    public const MYTHRIL_INGOT_ITEM_ID = 5065;
    public const VARNISH_RECIPE_ID = 1586;
    public const VARNISH_ITEM_ID = 7017;
    public const LINSEED_OIL_RECIPE_ID = 1147;
    public const LINSEED_OIL_ITEM_ID = 5485;

    public function __construct(private Generator $faker)
    {
    }

    public function fetchRecipe(int $recipeID): array
    {
        if ($recipeID === self::WOODEN_LOFT_RECIPE_ID) {
            $json = file_get_contents('app\Http\Clients\XIV\data\recipe\3798.json');
            $data = json_decode($json, true);
            return $data;
        } elseif ($recipeID === self::ROSEWOOD_LUMBER_RECIPE_ID) {
            $json = file_get_contents('app\Http\Clients\XIV\data\recipe\1092.json');
            $data = json_decode($json, true);
            return $data;
        } elseif ($recipeID === self::MYTHRIL_RIVETS_RECIPE_ID) {
            $json = file_get_contents('app\Http\Clients\XIV\data\recipe\139.json');
            $data = json_decode($json, true);
            return $data;
        } elseif ($recipeID === self::MYTHRIL_INGOT_RECIPE_ID) {
            $json = file_get_contents('app\Http\Clients\XIV\data\recipe\135.json');
            $data = json_decode($json, true);
            return $data;
        } elseif ($recipeID === self::VARNISH_RECIPE_ID) {
            $json = file_get_contents('app\Http\Clients\XIV\data\recipe\1586.json');
            $data = json_decode($json, true);
            return $data;
        } elseif ($recipeID === self::LINSEED_OIL_RECIPE_ID) {
            $json = file_get_contents('app\Http\Clients\XIV\data\recipe\1147.json');
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
        $map = [
            self::WOODEN_LOFT_ITEM_ID => self::WOODEN_LOFT_RECIPE_ID,
            self::ROSEWOOD_LUMBER_ITEM_ID => self::ROSEWOOD_LUMBER_RECIPE_ID,
            self::MYTHRIL_RIVETS_ITEM_ID => self::MYTHRIL_RIVETS_RECIPE_ID,
            self::MYTHRIL_INGOT_ITEM_ID => self::MYTHRIL_INGOT_RECIPE_ID,
            self::VARNISH_ITEM_ID => self::VARNISH_RECIPE_ID,
            self::LINSEED_OIL_ITEM_ID => self::LINSEED_OIL_RECIPE_ID,
        ];

        $item = XIVItem::hydrate([
            'ID' => $itemID,
            'Name' => $this->faker->word,
            'Icon' => $this->faker->word,
        ]);

        if (array_key_exists($itemID, $map)) {
            $item->Recipes = [
                (object)[
                    'ID' => $map[$itemID] ?? null,
                ]
            ];
        }

        return $item;
    }
}
