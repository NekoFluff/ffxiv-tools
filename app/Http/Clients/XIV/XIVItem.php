<?php

namespace App\Http\Clients\XIV;

use Illuminate\Support\Collection;

class XIVItem
{
    /**
     * @var Collection<int, XIVRecipe>
     */
    public Collection $Recipes;

    public function __construct(public int $ID, public string $Name, public string $Icon)
    {
        $this->Recipes = new Collection();
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function hydrateFromItemFetchResponse(array $data): self
    {
        $item = new self(
            $data['row_id'],
            $data['fields']['Name'],
            $data['fields']['Icon']['path']
        );

        return $item;
    }
}
