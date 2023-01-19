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

    protected ?Collection $items;

    public function __construct(protected Container $container)
    {
        $this->items = new Collection();
    }

    public function boot()
    {
        rescue(fn () => $this->ensureOptionsIsLoaded());
    }

    public function has(string $key): bool
    {
        $this->get($key);

        return $this->items->has($key);
    }

    public function get(string $key, mixed $default = null): mixed
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

    public function set(array|string $key, $value = null, bool $autoload = null, bool $isLock = null): void
    {
        if (is_array($key)) {
            $this->setMany($key, $autoload, $isLock);
            return;
        }

        $option = $this->items->has($key)
            ? $this->items->get($key)
            : Option::query()->firstOrNew(['name' => $key]);

        if ($option->locked) {
            return;
        }

        $option->payload = $value;
        $option->locked = is_null($isLock)
            ? ($option->exists ? $option->locked : false)
            : $isLock;
        $option->autoload = is_null($autoload)
            ? ($option->exists ? $option->autoload : false)
            : $autoload;

        $this->items->put($key, $option);
    }

    public function setMany(array $values, bool $autoload = null, bool $isLock = null): void
    {
        Collection::make($values)
            ->map(fn ($value, $key) => [
                'name' => $key,
                'payload' => $value,
            ])
            ->each(fn ($setting) => $this->set(
                $setting['name'],
                $setting['payload'],
                $autoload,
                $isLock
            ));
    }

    public function lock(array|string $key): void
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
    }

    public function unlock(array|string $key): void
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
    }

    public function remove(array|string $key): void
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

        $this->items->forget($key);
    }

    public function all(): array
    {
        return $this->items->mapWithKeys(fn (Option $option) => [$option->name => $option->payload])->toArray();
    }

    public function reload(): void
    {
        $this->items = Option::query()
            ->where('autoload', true)
            ->get()
            ->keyBy('name');
    }

    public function save(): void
    {
        $values = $this->items
            ->map(fn (Option $option, string $name) => [
                'name' => $name,
                'payload' => $option->getAttributes()['payload'],
                'locked' => $option->locked ? 1 : 0,
                'autoload' => $option->autoload ? 1 : 0,
            ])
            ->values()
            ->toArray();

        Option::query()->upsert(
            $values,
            ['name'],
            ['payload', 'locked', 'autoload']
        );

        Option::query()->whereNotIn(
            'name',
            $this->items->keys()->toArray()
        )->delete();
    }

    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->set($offset);
    }

    public function toArray(): array
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
}
