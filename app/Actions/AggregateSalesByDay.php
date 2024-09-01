<?php

namespace App\Actions;

use App\Models\Sale;
use Illuminate\Support\Collection;

class AggregateSalesByDay
{
    /**
     * Returns Sales aggregated daily for the last week
     *
     * @param  Collection<int,Sale>  $sales
     * @return Collection<int,array{date:(int|string), quantity: mixed, avg_price: float|int|null, median_price: float|int|null, min_price: mixed, max_price: mixed}>
     */
    public function __invoke(Collection $sales): Collection
    {
        $aggregatedSales = collect($sales)->groupBy(
            function ($entry) {
                return $entry['timestamp']->format('Y-m-d');
            }
        )->map(
            function ($entries, $date) {
                return [
                    'date' => $date,
                    'quantity' => collect($entries)->sum('quantity'),
                    'avg_price' => collect($entries)->avg('price_per_unit'),
                    'median_price' => collect($entries)->median('price_per_unit'),
                    'min_price' => collect($entries)->min('price_per_unit'),
                    'max_price' => collect($entries)->max('price_per_unit'),
                ];
            }
        )->reverse()->values();

        // Add missing days in the last week without quantity
        $lastWeekDates = [
            date('Y-m-d', intval(now()->subDays(0)->timestamp)),
            date('Y-m-d', intval(now()->subDays(1)->timestamp)),
            date('Y-m-d', intval(now()->subDays(2)->timestamp)),
            date('Y-m-d', intval(now()->subDays(3)->timestamp)),
            date('Y-m-d', intval(now()->subDays(4)->timestamp)),
            date('Y-m-d', intval(now()->subDays(5)->timestamp)),
            date('Y-m-d', intval(now()->subDays(6)->timestamp)),
            date('Y-m-d', intval(now()->subDays(7)->timestamp)),
        ];
        $missingDates = collect($lastWeekDates)->diff($aggregatedSales->pluck('date'));
        $missingDates->each(
            function ($date) use (&$aggregatedSales) {
                $aggregatedSales->push(
                    [
                        'date' => $date,
                        'quantity' => 0,
                        'median_price' => 0,
                        'avg_price' => 0,
                        'min_price' => 0,
                        'max_price' => 0,
                    ]
                );
            }
        );

        return $aggregatedSales->sortBy('date')->values();
    }
}
