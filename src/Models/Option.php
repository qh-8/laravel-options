<?php

namespace Qh\LaravelOptions\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $fillable = [
        'name',
        'payload',
    ];

    protected $casts = [
        'payload' => OptionPayloadCaster::class,
        'locked' => 'bool',
        'autoload' => 'bool',
    ];
}
