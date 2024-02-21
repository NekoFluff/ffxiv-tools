<?php

namespace App\Http\Controllers;

use App\Http\Clients\Universalis\UniversalisClient;
use App\Models\Item;
use App\Models\Listing;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class UniversalisController extends Controller
{
    private UniversalisClient $universalisClient;

    public function __construct()
    {
        $this->universalisClient = new UniversalisClient();
    }

    /** @return array<int, Collection<Listing>> */
    public function getMarketBoardListings(string $server, array $item_ids): array
    {
        $mb_data_arr = $this->universalisClient->fetchMarketBoardListingsWithRetries($server, $item_ids);

        $result = [];
        foreach ($mb_data_arr as $key => $item) {
            $result[$key] = $this->updateListings($item->itemID, $item->listings);
            $this->processMarketBoardSaleHistory($item->itemID, $item->recentHistory);
        }
        return $result;
    }

    /** @return Collection<Listing> */
    private function updateListings(int $itemID, array $listings): Collection
    {
        Item::where('id', $itemID)->first()?->listings()->delete();

        if (empty($listings)) {
            return collect([]);
        }

        $listings = collect($listings)->map(
            function ($entry) use ($itemID) {
                return [
                    "id" => $entry->listingID,
                    "item_id" => $itemID,
                    "retainer_name" => $entry->retainerName,
                    "retainer_city" => $entry->retainerCity,
                    "quantity" => $entry->quantity,
                    "price_per_unit" => $entry->pricePerUnit,
                    "hq" => $entry->hq,
                    "total" => $entry->total,
                    "tax" => $entry->tax,
                    "last_review_time" => Carbon::createFromTimestamp($entry->lastReviewTime),
                ];
            }
        );

        $count = Listing::upsert(
            $listings->toArray(),
            ['id'],
            ['retainer_name', 'retainer_city', 'quantity', 'price_per_unit', 'hq', 'total', 'tax', 'last_review_time']
        );

        return Listing::where('item_id', $itemID)->orderBy('price_per_unit', 'asc')->limit($count)->get();
    }


    public function getLastWeekSaleCount(string $server, int $item_id): int
    {
        $cacheKey = "last_week_sale_count_{$item_id}";
        $sale_count = cache()->remember(
            $cacheKey,
            now()->addMinutes(60),
            function () use ($item_id, $server) {
                return $this->universalisClient->fetchLastWeekSaleCount($server, $item_id);
            }
        );
        return $sale_count;
    }

    /** @return Collection<array> */
    public function getMarketBoardHistory(string $server, string $item_id): Collection
    {
        $mb_history = $this->universalisClient->fetchMarketBoardHistory($server, $item_id);

        $this->processMarketBoardSaleHistory($item_id, $mb_history);

        $sales_count = $sales_count ?? 0;
        $mb_history = Sale::where('item_id', $item_id)->latest()->limit($sales_count)->get();

        return $this->translateToHistory($mb_history);
    }

    private function processMarketBoardSaleHistory(string $item_id, array $mb_history)
    {
        $mb_history = collect($mb_history)->map(
            function ($entry) use ($item_id) {
                return [
                    "item_id" => $item_id,
                    "quantity" => $entry->quantity,
                    "price_per_unit" => $entry->pricePerUnit,
                    "buyer_name" => $entry->buyerName,
                    "timestamp" => Carbon::createFromTimestamp($entry->timestamp),
                    "hq" => $entry->hq,
                ];
            }
        );

        Sale::upsert(
            $mb_history->toArray(),
            ['item_id', 'timestamp', 'buyer_name'],
            ['quantity', 'price_per_unit', 'hq']
        );
    }

    /**
     *
     * @param Collection<Sale> $sales
     * @return Collection<array>
     *  */
    public function translateToHistory(Collection $sales): Collection
    {
        $mb_history = collect($sales)->groupBy(
            function ($entry) {
                return $entry["timestamp"]->format('Y-m-d');
            }
        )->map(
            function ($entries, $date) {
                return [
                    "date" => $date,
                    "quantity" => collect($entries)->sum("quantity"),
                    "avg_price" => collect($entries)->avg("price_per_unit"),
                    "median_price" => collect($entries)->median("price_per_unit"),
                    "min_price" => collect($entries)->min("price_per_unit"),
                    "max_price" => collect($entries)->max("price_per_unit"),
                ];
            }
        )->reverse()->values();

        // Add missing days in the last week without quantity
        $lastWeekDates = [
            date('Y-m-d', now()->subDays(0)->timestamp),
            date('Y-m-d', now()->subDays(1)->timestamp),
            date('Y-m-d', now()->subDays(2)->timestamp),
            date('Y-m-d', now()->subDays(3)->timestamp),
            date('Y-m-d', now()->subDays(4)->timestamp),
            date('Y-m-d', now()->subDays(5)->timestamp),
            date('Y-m-d', now()->subDays(6)->timestamp),
            date('Y-m-d', now()->subDays(7)->timestamp),
        ];
        $missingDates = collect($lastWeekDates)->diff($mb_history->pluck('date'));
        $missingDates->each(
            function ($date) use (&$mb_history) {
                $mb_history->push(
                    [
                        "date" => $date,
                        "quantity" => 0,
                        "median_price" => 0,
                        "avg_price" => 0,
                        "min_price" => 0,
                        "max_price" => 0,
                    ]
                );
            }
        );

        $mb_history = $mb_history->sortBy('date')->values();

        return $mb_history;
    }

}
