<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class UniversalisController extends Controller
{
    /** @return array<int, Collection<Listing>> */
    public function getMarketBoardData(string $server, array $item_ids): array
    {
        $item_ids = array_unique($item_ids);
        sort($item_ids);

        logger("Fetching market board data for server {$server}." . " | Items: " . implode(",", $item_ids));
        $maxRetries = 3;
        $retryCount = 0;
        $mb_data = "";

        while ($retryCount < $maxRetries) {
            try {
                $mb_data = file_get_contents(
                    "https://universalis.app/api/v2/{$server}/" . implode(",", $item_ids)
                );
            } catch (\Exception $e) {
                logger("Failed to retrieve market board data for server {$server}");
            }

            if ($mb_data !== false) {
                break; // Request succeeded, exit the loop
            }

            $retryCount++;
            sleep(1); // Wait for 1 second before retrying
        }

        if ($mb_data === false) {
            // Handle the failure case here
            logger("Failed to retrieve market board data for server {$server}");
            return [];
        }

        logger("Retrieved market board data for server {$server}");
        $mb_data_arr = json_decode($mb_data, true) ?? [];
        $mb_data_arr = $mb_data_arr["items"] ?? [
            $mb_data_arr['itemID'] => $mb_data_arr
        ];

        $result = [];
        foreach ($mb_data_arr as $key => $item) {
            $result[$key] = $this->updateListings($item['itemID'], $item["listings"]);
        }
        return $result;
    }

    /** @return Collection<Listing> */
    private function updateListings(int $itemID, array $listings): Collection
    {
        if (empty($listings)) {
            return [];
        }

        $listings = collect($listings)->map(
            function ($entry) use ($itemID) {
                return [
                    "id" => $entry["listingID"],
                    "item_id" => $itemID,
                    "retainer_name" => $entry["retainerName"],
                    "retainer_city" => $entry["retainerCity"],
                    "quantity" => $entry["quantity"],
                    "price_per_unit" => $entry["pricePerUnit"],
                    "hq" => $entry["hq"],
                    "total" => $entry["total"],
                    "tax" => $entry["tax"],
                    "last_review_time" => Carbon::createFromTimestamp($entry["lastReviewTime"]),
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
                logger("Fetching last week sale count for item {$item_id}");
                $mb_history = file_get_contents("https://universalis.app/api/v2/history/{$server}/{$item_id}");
                $mb_history = json_decode($mb_history, true);
                return collect($mb_history["entries"])->map(
                    function ($entry) {
                        return $entry["quantity"];
                    }
                )->sum();
            }
        );
        return $sale_count;
    }

    /** @return Collection<array> */
    public function getMarketBoardHistory(string $server, string $item_id): Collection
    {
        $data = file_get_contents("https://universalis.app/api/v2/history/{$server}/{$item_id}");
        $data = json_decode($data, true);
        $item_id = $data["itemID"];
        $mb_history = $data["entries"];
        $mb_history = collect($mb_history)->map(
            function ($entry) use ($item_id) {
                return [
                    "item_id" => $item_id,
                    "quantity" => $entry["quantity"],
                    "price_per_unit" => $entry["pricePerUnit"],
                    "buyer_name" => $entry["buyerName"],
                    "timestamp" => Carbon::createFromTimestamp($entry["timestamp"]),
                    "hq" => $entry["hq"],
                ];
            }
        );

        $sales_count = Sale::upsert(
            $mb_history->toArray(),
            ['item_id', 'timestamp', 'buyer_name'],
            ['quantity', 'price_per_unit', 'hq']
        );

        $mb_history = Sale::where('item_id', $item_id)->latest()->limit($sales_count)->get();

        return $this->translateToHistory($mb_history);
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
                    ]
                );
            }
        );

        $mb_history = $mb_history->sortBy('date')->values();

        return $mb_history;
    }

}
