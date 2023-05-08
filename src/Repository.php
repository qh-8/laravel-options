<?php

namespace Qh\LaravelOptions;

use ArrayAccess;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use JsonSerializable;
use Qh\LaravelOptions\Contracts\Repository as RepositoryContract;
use Qh\LaravelOptions\Models\Option;

class Repository implements ArrayAccess, Arrayable, Jsonable, JsonSerializable, RepositoryContract
{
    protected bool $initialized = false;

    protected Collection $items;

    protected array $willRemoved = [];

    protected bool $eagerLoad = false;

    protected bool $onlyAutoload = true;

    protected string $model;

    public function __construct(Config $config)
    {
        $this->items = new Collection();
        $this->eagerLoad = $config->get('options.eager_load', false);
        $this->onlyAutoload = $config->get('options.only_autoload', true);
        $this->model = $config->get('options.model', Option::class);
    }

    public function boot(): void
    {
        rescue(fn () => $this->ensureOptionsIsLoaded());
    }

    public function setEagerLoad(bool $eagerLoad): void
    {
        $this->eagerLoad = $eagerLoad;
    }

    public function setOnlyAutoload(bool $onlyAutoload): void
    {
        $this->onlyAutoload = $onlyAutoload;
    }

    public function has(string $key): bool
    {
        $this->get($key);

        return $this->items->has($key);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $this->boot();

        if ($this->items->has($key)) {
            return $this->items->get($key)->payload;
        }

        if (! $this->eagerLoad) {
            return $default;
        }

        $item = $this->newQuery()->where('name', $key)->first();

        if ($item) {
            $this->items->put($key, $item);

            return $item->payload;
        }

        return $default;
    }

    public function set(array|string $key, $value = null, bool $autoload = null, bool $isLock = null): void
    {
        $this->boot();

        if (is_array($key)) {
            $this->setMany($key, $autoload, $isLock);
            return;
        }

        $option = $this->items->has($key)
            ? $this->items->get($key)
            : new Option(['name' => $key]);

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
        $this->willRemoved[] = $key;
    }

    public function all(): array
    {
        $this->boot();

        return $this->items->mapWithKeys(fn (Option $option) => [$option->name => $option->payload])->toArray();
    }

    public function reload(): void
    {
        $this->items = $this->newQuery()
            ->when($this->onlyAutoload, fn ($query) => $query->where('autoload', true))
            ->get()
            ->keyBy('name');
    }

    public function save(): void
    {
        $values = $this->items
            ->map(fn (Model $option, string $name) => [
                'name' => $name,
                'payload' => $option->getAttributes()['payload'],
                'locked' => $option->locked ? 1 : 0,
                'autoload' => $option->autoload ? 1 : 0,
            ])
            ->values()
            ->all();

        $this->newQuery()->upsert(
            $values,
            ['name'],
            ['payload', 'locked', 'autoload']
        );

        $this->newQuery()
            ->whereIn('name', $this->willRemoved)
            ->delete();

        $this->willRemoved = [];
    }

    public function model(): Model
    {
        return app($this->model);
    }

    public function newQuery(): Builder
    {
        return $this->model()->newQuery();
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

    public function toJson($options = 0): bool|string
    {
        return json_encode($this->toArray());
    }

    public function jsonSerialize(): bool|string
    {
        return $this->toJson();
    }

    protected function ensureOptionsIsLoaded(): void
    {
        if ($this->initialized) {
            return;
        }

        $this->initialized = true;

        $this->reload();
    }
}
