<?php

namespace Qh\LaravelOptions\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void boot()
 * @method static bool has(string $key)
 * @method static mixed|null get(string $key, mixed|null $default = null)
 * @method static void set(array|string $key, mixed $value = null, bool|null $autoload = null, bool|null $isLock = null)
 * @method static void setMany(array $values, bool|null $autoload = null, bool|null $isLock = null)
 * @method static void lock(array|string $key)
 * @method static void unlock(array|string $key)
 * @method static void remove(array|string $key)
 * @method static array all()
 * @method static void reload()
 * @method static void save()
 * @method static array toArray()
 * @method static void toJson(int $options = 0)
 *
 * @see \Qh\LaravelOptions\Repository
 */
class Option extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'options';
    }
}
