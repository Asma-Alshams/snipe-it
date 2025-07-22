<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class MaintenanceAcceptance extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    protected $casts = [
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
    ];

    protected $fillable = [
        'maintenance_id',
        'assigned_to_id',
    ];

    /**
     * Get the mail recipient from the config
     *
     * @return mixed|string|null
     */
    public function routeNotificationForMail()
    {
        // At this point the endpoint is the same for everything.
        //  In the future this may want to be adapted for individual notifications.
        $recipients_string = explode(',', Setting::getSettings()->alert_email);
        $recipients = array_map('trim', $recipients_string);

        return array_filter($recipients);
    }

    /**
     * The maintenance that needs acceptance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function maintenance()
    {
        return $this->belongsTo(AssetMaintenance::class);
    }

    /**
     * The user that the maintenance is assigned to
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    /**
     * Is this maintenance acceptance pending?
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->accepted_at == null && $this->declined_at == null;
    }

    /**
     * Was the maintenance assigned to this user?
     *
     * @param  User $user
     * @return bool
     */
    public function isAssignedTo(User $user)
    {
        return $this->assignedTo?->is($user);
    }

    /**
     * Accept the maintenance
     *
     * @param string $signature_filename
     * @param string $note
     */
    public function accept($signature_filename = null, $note = null)
    {
        $this->accepted_at = now();
        $this->signature_filename = $signature_filename;
        $this->note = $note;
        $this->save();
    }

    /**
     * Decline the maintenance acceptance
     *
     * @param string $signature_filename
     * @param string $note
     */
    public function decline($signature_filename = null, $note = null)
    {
        $this->declined_at = now();
        $this->note = $note;
        $this->signature_filename = $signature_filename;
        $this->save();
    }

    /**
     * Filter maintenance acceptances by the user
     *
     * @param  Illuminate\Database\Eloquent\Builder $query
     * @param  User                                 $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser(Builder $query, User $user)
    {
        return $query->where('assigned_to_id', $user->id);
    }

    /**
     * Filter to only get pending acceptances
     *
     * @param  Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending(Builder $query)
    {
        return $query->whereNull('accepted_at')->whereNull('declined_at');
    }
}
