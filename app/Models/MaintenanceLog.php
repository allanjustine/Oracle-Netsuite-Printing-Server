<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    protected $guarded = [];

    protected $appends = ['maintenance_time'];

    public function getMaintenanceTimeAttribute()
    {
        return (int) now()->diffInSeconds($this->finished_at);
    }
}
