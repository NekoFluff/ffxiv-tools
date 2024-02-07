<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient
{
    public int $item_id;

    public string $name;

    public float $amount;

    public int $market_cost;

    public int $vendor_cost;

    public string $icon;

    public ?Recipe $recipe;
}
