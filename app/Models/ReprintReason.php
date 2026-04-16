<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReprintReason extends Model
{
    protected $guarded = [];

    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }
}
