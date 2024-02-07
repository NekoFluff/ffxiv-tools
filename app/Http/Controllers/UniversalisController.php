<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UniversalisController extends Controller
{
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
        return $mb_data_arr;
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

    public function getMarketBoardHistory(string $server, string $item_id)
    {
        $mb_history = file_get_contents("https://universalis.app/api/v2/history/{$server}/{$item_id}");
        $mb_history = json_decode($mb_history, true)['entries'];
        $mb_history = collect($mb_history)->groupBy(
            function ($entry) {
                return date("Y-m-d", $entry["timestamp"]);
            }
        )->map(
            function ($entries, $date) {
                return [
                    "date" => $date,
                    "quantity" => collect($entries)->sum("quantity"),
                ];
            }
        )->reverse()->values();
        return $mb_history;
    }

}
