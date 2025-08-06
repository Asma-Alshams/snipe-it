<?php

namespace App\Console\Commands;

use App\Models\AssetMaintenance;
use App\Models\Setting;
use App\Notifications\MaintenanceHalfwayNotification;
use App\Notifications\MaintenanceCompletionNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendMaintenanceHalfwayNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:maintenance-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email notifications to users when maintenance records reach 50% completion or completion date.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $settings = Setting::getSettings();

        if (!$settings->alerts_enabled) {
            $this->info('Alerts are disabled in settings. No notifications will be sent.');
            return 0;
        }

        $this->info('Checking for maintenance records at 50% completion and completion date...');

        $maintenances = AssetMaintenance::with(['adminuser', 'asset'])
            ->whereNotNull('completion_date')
            ->whereNotNull('start_date')
            ->whereNotNull('created_by')
            ->where('start_date', '!=', '0000-00-00')
            ->where('completion_date', '!=', '0000-00-00')
            ->get();

        $halfwayCount = 0;
        $completionCount = 0;
        $today = Carbon::now();

        // Group maintenances by user for halfway notifications
        $halfwayMaintenancesByUser = collect();
        $completionMaintenancesByUser = collect();

        foreach ($maintenances as $maintenance) {
            // Skip if no creator user or no email
            if (!$maintenance->adminuser || !$maintenance->adminuser->email) {
                continue;
            }

            $startDate = Carbon::parse($maintenance->start_date);
            $completionDate = Carbon::parse($maintenance->completion_date);
            
            // Check for halfway point
            $halfwayDate = $startDate->copy()->addDays($startDate->diffInDays($completionDate) / 2);
            $toleranceStart = $halfwayDate->copy()->subDay();
            $toleranceEnd = $halfwayDate->copy()->addDay();
            
            if ($today->between($toleranceStart, $toleranceEnd)) {
                $halfwayMaintenancesByUser->push([
                    'maintenance' => $maintenance,
                    'user' => $maintenance->adminuser,
                    'type' => 'halfway'
                ]);
            }
            
            // Check for completion date
            if ($today->isSameDay($completionDate)) {
                $completionMaintenancesByUser->push([
                    'maintenance' => $maintenance,
                    'user' => $maintenance->adminuser,
                    'type' => 'completion'
                ]);
            }
        }

        // Group by user and send consolidated emails
        $halfwayByUser = $halfwayMaintenancesByUser->groupBy('user.id');
        $completionByUser = $completionMaintenancesByUser->groupBy('user.id');

        // Send halfway notifications
        foreach ($halfwayByUser as $userId => $userMaintenances) {
            $user = $userMaintenances->first()['user'];
            $maintenanceList = $userMaintenances->pluck('maintenance');
            
            try {
                $user->notify(new MaintenanceHalfwayNotification($maintenanceList));
                $this->info("Sent halfway notification for {$maintenanceList->count()} maintenance record(s) to {$user->email}");
                $halfwayCount += $maintenanceList->count();
            } catch (\Exception $e) {
                $this->error("Failed to send halfway notification to user {$user->email}: " . $e->getMessage());
            }
        }

        // Send completion notifications
        foreach ($completionByUser as $userId => $userMaintenances) {
            $user = $userMaintenances->first()['user'];
            $maintenanceList = $userMaintenances->pluck('maintenance');
            
            try {
                $user->notify(new MaintenanceCompletionNotification($maintenanceList));
                $this->info("Sent completion notification for {$maintenanceList->count()} maintenance record(s) to {$user->email}");
                $completionCount += $maintenanceList->count();
            } catch (\Exception $e) {
                $this->error("Failed to send completion notification to user {$user->email}: " . $e->getMessage());
            }
        }

        $this->info("Processed {$maintenances->count()} maintenance records.");
        $this->info("Sent {$halfwayCount} halfway notifications.");
        $this->info("Sent {$completionCount} completion notifications.");

        return 0;
    }
} 