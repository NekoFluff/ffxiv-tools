<?php

namespace App\Http\Clients\XIV;

class XIVItem
{
    public string $ID;

    public string $Name;

    public string $Icon;

    public array $Recipes;

    public function hydrate(array $data): void
    {
        $this->ID = $data['ID'];
        $this->Name = $data['Name'];
        $this->Icon = $data['Icon'];
        $this->Recipes = $data['Recipes'];
    }
}
