import { Item } from '@/types/item';
import { Recipe } from '@/types/recipe';

export type Ingredient = {
    item: Item,
    crafting_recipe: Recipe,
    amount: number,
    recipe_id: number,
    item_id: number,
}