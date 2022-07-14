<?php

namespace Qh\LaravelOptions\Models;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Str;

class OptionPayloadCaster implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        return Str::isJson($value) ? json_decode($value, true) : $value;
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return json_encode($value);
    }
}
