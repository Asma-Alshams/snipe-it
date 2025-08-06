<?php

namespace App\Notifications;

use App\Helpers\Helper;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpectedCheckinNotification extends Notification
{
    use Queueable;
    /**
     * @var
     */
    private $assets;

    /**
     * Create a new notification instance.
     */
    public function __construct($assets)
    {
        // Accepts a collection or array of assets, or a single asset
        if (!is_array($assets) && !$assets instanceof \Illuminate\Support\Collection) {
            $this->assets = collect([$assets]);
        } else {
            $this->assets = collect($assets);
        }
    }

    public function via()
    {
        return ['mail'];
    }

    public function toMail()
    {
        $first = $this->assets->first();
        $subject = trans('mail.Expected_Checkin_Notification', ['count' => $this->assets->count()]);

        return (new MailMessage)->markdown('notifications.markdown.expected-checkin', [
            'assets' => $this->assets,
            'single' => $this->assets->count() === 1,
            'date' => \App\Helpers\Helper::getFormattedDateObject($first->expected_checkin, 'date', false),
            'asset' => $first->present()->name(),
            'serial' => $first->serial,
            'asset_tag' => $first->asset_tag,
        ])->subject($subject);
    }
}
