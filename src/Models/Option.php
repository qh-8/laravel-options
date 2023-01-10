<?php

namespace Qh\LaravelOptions\Models;

use Illuminate\Database\Eloquent\Model;
use Qh\LaravelOptions\Casts\OptionPayload;

class Option extends Model
{
    protected $fillable = [
        'name',
        'payload',
    ];

    protected $casts = [
        'payload' => OptionPayload::class,
        'locked' => 'bool',
        'autoload' => 'bool',
    ];
}
