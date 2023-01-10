<?php

namespace Qh\LaravelOptions\Contracts;

interface Repository
{
    public function get(string $key, $default = null): mixed;

    public function set(array|string $key, $value = null, bool $autoload = false, bool $isLock = false): void;

    public function setMany(array $values): void;

    public function lock(array|string $key): void;

    public function unlock(array|string $key): void;

    public function remove(array|string $key): void;

    public function all(): array;

    public function reload(): void;
}
