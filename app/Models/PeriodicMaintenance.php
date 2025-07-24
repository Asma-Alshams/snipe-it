<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodicMaintenance extends Model
{
    use HasFactory;

    protected $table = 'periodic_maintenances';

    protected $fillable = [
        // Add fields as needed for reporting/metadata
    ];
}

