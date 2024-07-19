<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waypoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'x',
        'y',
        'z',
        'connected'
    ];

    protected $casts = [
        'connected' => 'array'
    ];
}
