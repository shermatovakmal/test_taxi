<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderRoute extends Model
{
    protected $table = "order_route";

    protected $fillable = [
        'order_id',
        'latitude_start',
        'longitude_start',
        'latitude_end',
        'longitude_end',
    ];
}
