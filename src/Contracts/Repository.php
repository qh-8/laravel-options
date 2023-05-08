<?php

namespace Qh\LaravelOptions\Contracts;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface Repository
{
    public function get(string $key, $default = null): mixed;

    public function set(array|string $key, $value = null, bool $autoload = false, bool $isLock = false): void;

    public function setMany(array $values): void;

    public function lock(array|string $key): void;

    public function unlock(array|string $key): void;

    public function remove(array|string $key): void;

    public function all(): array;

    public function save(): void;

    public function reload(): void;

    public function model(): Model;

    public function newQuery(): Builder;
}
