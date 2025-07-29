<?php

namespace App\Console\Commands;

use App\Models\AssetMaintenance;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UpdateMaintenanceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update maintenance statuses based on completion dates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting maintenance status update...');

        // Get maintenances with completion dates in the past that are not already completed
        $maintenances = AssetMaintenance::where('completion_date', '<=', now())
            ->whereNotNull('completion_date')
            ->get();

        $updatedCount = 0;

        foreach ($maintenances as $maintenance) {
            // Check if the maintenance is past completion date but should remain under maintenance
            if (now()->isAfter($maintenance->completion_date)) {
                // The status is now manually controlled - don't automatically mark as completed
                // This command can be used to log or perform additional actions
                $this->line("Maintenance ID {$maintenance->id} has completion date {$maintenance->completion_date} - status remains under maintenance for manual completion");
                $updatedCount++;
            }
        }

        $this->info("Processed {$updatedCount} maintenances with past completion dates.");
        $this->info('Maintenance status update completed!');
    }
}
