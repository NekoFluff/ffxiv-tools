<?php

namespace App\Console\Commands;

use App\Models\Enums\Server;
use App\Services\FFXIVService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RecalculateCostsForRecipe extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipe:recalculateCosts
                            {item* : The ID of the item}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate costs for a recipe';

    /**
     * Prompt for missing arguments or options.
     */
    protected function promptForMissingArguments(InputInterface $input, OutputInterface $output): array
    {
        return [
            'item' => ['Which item ID would you like to recalculate recipe costs for?'],
        ];
    }

    protected FFXIVService $ffxivService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(FFXIVService $ffxivService)
    {
        parent::__construct();

        $this->ffxivService = $ffxivService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /** @var array<int> $itemIDs */
        $itemIDs = $this->argument('item');
        foreach ($itemIDs as $itemID) {
            $itemID = intval($itemID);
            $recipe = $this->ffxivService->getRecipeByItemID($itemID);

            if (! $recipe) {
                $this->error("Recipe with Item ID #$itemID not found");

                return 1;
            }
            $this->ffxivService->updateRecipeCosts(Server::GOBLIN, $recipe);
        }
        $this->info('Recipe costs recalculated successfully');

        return 0;
    }
}
