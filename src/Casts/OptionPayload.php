<?php

namespace Qh\LaravelOptions\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Str;

class OptionPayload implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): mixed
    {
        return Str::isJson($value) ? json_decode($value, true) : $value;
    }

    public function set($model, string $key, $value, array $attributes): string|null
    {
        return json_encode($value);
    }
}
