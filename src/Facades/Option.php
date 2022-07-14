<?php

namespace Qh\LaravelOptions\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed get(string $key, $detault = null)
 * @method static void set(string|array $key, $value = null, bool $autoload = false, bool $isLock = false)
 * @method static void setMany(array $options)
 * @method static void lock(string $key)
 * @method static void unlock(string $key)
 * @method static void remove(string $key)
 * @method static array all()
 * @method static array toArray()
 * @method static string toJson($options = 0)
 * @method static void reload()
 *
 * @see \Qh\LaravelOptions\Repository
 */
class Option extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'options';
    }
}
