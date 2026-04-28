<?php

namespace App\Console\Commands;

use App\Services\PromotionService;
use Illuminate\Console\Command;

class ExpirePromotions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'promotions:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expirer automatiquement les promotions dont la date de fin est dépassée';

    /**
     * Execute the console command.
     */
    public function handle(PromotionService $promotionService): int
    {
        $count = $promotionService->expireOverduePromotions();

        $this->info("{$count} promotion(s) expirée(s) avec succès.");

        return Command::SUCCESS;
    }
}
