<?php

namespace Tests\Feature\Console;

use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\User;
use App\Notifications\MaintenanceHalfwayNotification;
use App\Notifications\MaintenanceCompletionNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendMaintenanceNotificationsTest extends TestCase
{
    public function testMaintenanceHalfwayCommand()
    {
        Notification::fake();

        // Create a user with email
        $user = User::factory()->create(['email' => 'test@example.com']);
        
        // Create an asset
        $asset = Asset::factory()->create();
        
        // Create a maintenance record that is exactly at 50% completion
        $startDate = Carbon::now()->subDays(5);
        $completionDate = Carbon::now()->addDays(5);
        
        $maintenance = AssetMaintenance::factory()->create([
            'asset_id' => $asset->id,
            'created_by' => $user->id,
            'start_date' => $startDate->format('Y-m-d'),
            'completion_date' => $completionDate->format('Y-m-d'),
            'title' => 'Test Maintenance',
        ]);

        $this->artisan('snipeit:maintenance-notifications')->assertExitCode(0);

        Notification::assertSentTo(
            $user,
            MaintenanceHalfwayNotification::class,
            function ($notification) use ($maintenance) {
                return $notification->maintenance->id === $maintenance->id;
            }
        );
    }

    public function testMaintenanceCompletionCommand()
    {
        Notification::fake();

        // Create a user with email
        $user = User::factory()->create(['email' => 'test@example.com']);
        
        // Create an asset
        $asset = Asset::factory()->create();
        
        // Create a maintenance record that is at completion date
        $startDate = Carbon::now()->subDays(5);
        $completionDate = Carbon::now();
        
        $maintenance = AssetMaintenance::factory()->create([
            'asset_id' => $asset->id,
            'created_by' => $user->id,
            'start_date' => $startDate->format('Y-m-d'),
            'completion_date' => $completionDate->format('Y-m-d'),
            'title' => 'Test Completion Maintenance',
        ]);

        $this->artisan('snipeit:maintenance-notifications')->assertExitCode(0);

        Notification::assertSentTo(
            $user,
            MaintenanceCompletionNotification::class,
            function ($notification) use ($maintenance) {
                return $notification->maintenance->id === $maintenance->id;
            }
        );
    }

    public function testMaintenanceNotificationsCommandWithNoMatchingMaintenances()
    {
        Notification::fake();

        // Create a user with email
        $user = User::factory()->create(['email' => 'test@example.com']);
        
        // Create an asset
        $asset = Asset::factory()->create();
        
        // Create a maintenance record that is not at halfway or completion
        $startDate = Carbon::now()->subDays(10);
        $completionDate = Carbon::now()->addDays(10);
        
        $maintenance = AssetMaintenance::factory()->create([
            'asset_id' => $asset->id,
            'created_by' => $user->id,
            'start_date' => $startDate->format('Y-m-d'),
            'completion_date' => $completionDate->format('Y-m-d'),
            'title' => 'Test Maintenance',
        ]);

        $this->artisan('snipeit:maintenance-notifications')->assertExitCode(0);

        Notification::assertNotSentTo($user, MaintenanceHalfwayNotification::class);
        Notification::assertNotSentTo($user, MaintenanceCompletionNotification::class);
    }

    public function testMaintenanceNotificationsCommandWithUserWithoutEmail()
    {
        Notification::fake();

        // Create a user without email
        $user = User::factory()->create(['email' => '']);
        
        // Create an asset
        $asset = Asset::factory()->create();
        
        // Create a maintenance record that is at 50% completion
        $startDate = Carbon::now()->subDays(5);
        $completionDate = Carbon::now()->addDays(5);
        
        $maintenance = AssetMaintenance::factory()->create([
            'asset_id' => $asset->id,
            'created_by' => $user->id,
            'start_date' => $startDate->format('Y-m-d'),
            'completion_date' => $completionDate->format('Y-m-d'),
            'title' => 'Test Maintenance',
        ]);

        $this->artisan('snipeit:maintenance-notifications')->assertExitCode(0);

        Notification::assertNotSentTo($user, MaintenanceHalfwayNotification::class);
        Notification::assertNotSentTo($user, MaintenanceCompletionNotification::class);
    }
} 