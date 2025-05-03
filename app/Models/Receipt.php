<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    protected $guarded = [];

    protected function casts()
    {
        return [
            're_print' => 'boolean'
        ];
    }
}
