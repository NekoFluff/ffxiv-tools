<?php

namespace App\Http\Clients\XIV;

class XIVItem
{
    public string $ID;

    public string $Name;

    public string $Icon;

    public array $Recipes;

    public static function hydrate(array $data): self
    {
        $item = new self();

        $item->ID = $data['ID'];
        $item->Name = $data['Name'];
        $item->Icon = $data['Icon'];
        $item->Recipes = $data['Recipes'] ?? [];

        return $item;
    }
}
