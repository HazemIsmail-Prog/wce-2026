<?php

namespace App\Console\Commands;

use App\Services\WorldCupSyncService;
use Illuminate\Console\Command;

class SyncWorldCupGames extends Command
{
    /**
     * @var string
     */
    protected $signature = 'worldcup:sync';

    /**
     * @var string
     */
    protected $description = 'Sync World Cup 2026 schedule and results from openfootball';

    public function handle(WorldCupSyncService $syncService): int
    {
        $this->info('Syncing World Cup 2026 data...');

        try {
            $result = $syncService->sync();
        } catch (\Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Updated {$result['synced']} games. {$result['scored']} predictions scored.");

        return self::SUCCESS;
    }
}
