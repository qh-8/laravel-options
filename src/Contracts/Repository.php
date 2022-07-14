<?php

namespace Qh\LaravelOptions\Contracts;

interface Repository
{
    public function get(string $key, $detault = null);

    public function set(array|string $key, $value = null, bool $autoload = false, bool $isLock = false);

    public function setMany(array $options);

    public function lock(array|string $key);

    public function unlock(array|string $key);

    public function remove(array|string $key);

    public function all(): array;

    public function reload();
}
