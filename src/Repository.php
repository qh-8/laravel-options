<?php

namespace Qh\LaravelOptions;

use ArrayAccess;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Collection;
use Qh\LaravelOptions\Contracts\Repository as RepositoryContract;
use Qh\LaravelOptions\Models\Option;

class Repository implements ArrayAccess, Arrayable, Jsonable, RepositoryContract
{
    protected bool $initialized = false;

    protected ?Collection $items = null;

    public function __construct(protected Container $container)
    {
    }

    public function boot()
    {
        if (! $this->determineIfOptionsTableExists()) {
            return;
        }

        $this->ensureOptionsIsLoaded();
    }

    public function has(string $key): bool
    {
        $this->get($key);

        return $this->items->has($key);
    }

    public function get(string $key, $default = null): mixed
    {
        if (! $this->items) {
            return null;
        }

        if ($this->items->has($key)) {
            return $this->items->get($key)->payload;
        }

        $item = Option::query()->where('name', $key)->first();

        if ($item) {
            $this->items->put($key, $item);
            return $item->payload;
        }

        return $default;
    }

    public function set(array|string $key, $value = null, bool $autoload = false, bool $isLock = false)
    {
        if (is_array($key)) {
            $this->setMany($key);
            return;
        }

        $option = Option::query()->firstOrNew(['name' => $key]);

        if ($option->exists && $option->locked) {
            return;
        }

        $option->payload = $value;
        $option->locked = $isLock;
        $option->autoload = $autoload;
        $option->save();

        $this->items->put($key, $option);
    }

    public function setMany(array $values)
    {
        $existsLockedItems = Option::query()->where('locked', true)->get()->keyBy('name');

        $values = Collection::make($values)
            ->filter(fn ($_, $key) => !$existsLockedItems->has($key))
            ->map(fn ($option, $key) => [
                'name' => $key,
                'payload' => $option,
                'autoload' => false,
                'locked' => false,
            ])
            ->toArray();

        Option::query()->upsert($values, ['name'], ['payload']);
    }

    public function lock(array|string $key)
    {
        if (is_array($key)) {
            foreach ($key as $option) {
                $this->lock($option);
            }
            return;
        }

        if (! $this->has($key)) {
            return;
        }

        $item = $this->items->get($key);

        if ($item->locked) {
            return;
        }

        $item->locked = true;
        $item->save();
    }

    public function unlock(array|string $key)
    {
        if (is_array($key)) {
            foreach ($key as $option) {
                $this->unlock($option);
            }
            return;
        }

        if (! $this->has($key)) {
            return;
        }

        $item = $this->items->get($key);

        if (! $item->locked) {
            return;
        }

        $item->locked = false;
        $item->save();
    }

    public function remove(array|string $key)
    {
        if (is_array($key)) {
            foreach ($key as $option) {
                $this->remove($option);
            }
            return;
        }

        if (! $this->has($key)) {
            return;
        }

        $this->items->get($key)->delete();
        $this->items->forget($key);
    }

    public function all(): array
    {
        return $this->items->mapWithKeys(fn (Option $option) => [$option->name => $option->payload])->toArray();
    }

    public function reload()
    {
        $this->items = Option::query()->where('autoload', true)->get()->keyBy('name');
    }

    public function offsetExists($key): bool
    {
        return $this->has($key);
    }

    public function offsetGet($key): mixed
    {
        return $this->get($key);
    }

    public function offsetSet($key, $value): void
    {
        $this->set($key, $value);
    }

    public function offsetUnset($key): void
    {
        $this->set($key, null);
    }

    public function toArray()
    {
        return $this->all();
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray());
    }

    protected function ensureOptionsIsLoaded()
    {
        if ($this->initialized) {
            return;
        }

        $this->initialized = true;
        $this->reload();
    }

    protected function determineIfOptionsTableExists()
    {
        return $this->container['db.schema']->hasTable('options');
    }
}
