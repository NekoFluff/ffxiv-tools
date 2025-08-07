<?php

namespace App\Http\Clients\XIV;

class XIVIngredient
{
    public function __construct(public int $ItemID, public string $ItemName, public string $ItemIcon, public int $Amount)
    {
    }
}
