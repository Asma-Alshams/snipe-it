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
    public function __construct(public AssetMaintenance $maintenance)
    {
        //
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
        $maintenance = $this->maintenance;
        $asset = $maintenance->asset;
        
        return (new MailMessage)
            ->subject(trans('mail.maintenance_completion_subject', [
                'asset' => $asset->name ?? $asset->asset_tag,
                'maintenance' => $maintenance->title
            ]))
            ->markdown('notifications.markdown.maintenance-completion', [
                'maintenance' => $maintenance,
                'asset' => $asset,
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
        return [
            'maintenance_id' => $this->maintenance->id,
            'asset_id' => $this->maintenance->asset_id,
            'title' => $this->maintenance->title,
        ];
    }
} 