import { Item } from "@/types/item";
import { Ingredient } from "@/types/ingredient";

export type Recipe = {
    item: Item;
    ingredients: Ingredient[];
    amount_result: number;
    purchase_cost: number;
    market_craft_cost: number;
    optimal_craft_cost: number;
    class_job: string;
    class_job_level: number;
    class_job_icon: string;
};
