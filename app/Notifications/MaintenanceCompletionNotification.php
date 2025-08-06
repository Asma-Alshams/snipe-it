<?php

namespace App\Notifications;

use App\Models\AssetMaintenance;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceCompletionNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public $maintenances)
    {
        // Convert single maintenance to collection for consistency
        if (!is_array($maintenances) && !$maintenances instanceof \Illuminate\Support\Collection) {
            $this->maintenances = collect([$maintenances]);
        } else {
            $this->maintenances = collect($maintenances);
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $maintenance = $this->maintenances->first();
        $asset = $maintenance->asset;
        
        $subject = $this->maintenances->count() === 1 
            ? trans('mail.maintenance_completion_subject', [
                'asset' => $asset->name ?? $asset->asset_tag,
                'maintenance' => $maintenance->title
            ])
            : trans('mail.maintenance_completion_multiple_subject', [
                'count' => $this->maintenances->count()
            ]);
        
        return (new MailMessage)
            ->subject($subject)
            ->markdown('notifications.markdown.maintenance-completion', [
                'maintenances' => $this->maintenances,
                'maintenance' => $maintenance, // Keep for backward compatibility
                'asset' => $asset, // Keep for backward compatibility
                'user' => $notifiable,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $maintenance = $this->maintenances->first();
        return [
            'maintenance_id' => $maintenance->id,
            'asset_id' => $maintenance->asset_id,
            'title' => $maintenance->title,
            'count' => $this->maintenances->count(),
        ];
    }
} 