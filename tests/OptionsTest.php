<?php

namespace Qh\LaravelOptions\Tests;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Qh\LaravelOptions\Repository;

class OptionsTest extends TestCase
{
    use RefreshDatabase;

    protected Repository $repository;

    public function setUp(): void
    {
        parent::setUp();

        $now = Carbon::now();

        DB::table('options')->insert([
            ['name' => 'foo', 'payload' => 'bar', 'autoload' => 1, 'locked' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'bar', 'payload' => 'baz', 'autoload' => 1, 'locked' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'baz', 'payload' => 'bat', 'autoload' => 1, 'locked' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'boolean', 'payload' => json_encode(true), 'autoload' => 1, 'locked' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'array', 'payload' => json_encode(['xxx', 'yyy']), 'autoload' => 1, 'locked' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'associate', 'payload' => json_encode([
                'x' => 'xxx',
                'y' => 'yyy',
            ]), 'autoload' => 1, 'locked' => 0, 'created_at' => $now, 'updated_at' => $now
            ],

            ['name' => 'no_autoload', 'payload' => 'foo', 'autoload' => 0, 'locked' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'locked', 'payload' => 'foo', 'autoload' => 1, 'locked' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $this->repository = $this->app['options'];
        $this->repository->reload();
    }

    public function testGetBooleanValue()
    {
        $this->assertTrue(
            $this->repository->get('boolean')
        );
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(Repository::class, $this->repository);
    }

    public function testHasIsTrue()
    {
        $this->assertTrue($this->repository->has('foo'));
    }

    public function testHasIsFalse()
    {
        $this->assertFalse($this->repository->has('not-exist'));
    }

    public function testGet()
    {
        $this->assertSame('bar', $this->repository->get('foo'));
    }

    public function testGetWithDefault()
    {
        $this->assertSame('default', $this->repository->get('not-exist', 'default'));
    }

    public function testSet()
    {
        $this->repository->set('key', 'value');
        $this->assertSame('value', $this->repository->get('key'));
    }

    public function testSetArray()
    {
        $this->repository->set([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);
        $this->assertSame('value1', $this->repository->get('key1'));
        $this->assertSame('value2', $this->repository->get('key2'));
    }

    public function testSetLocked()
    {
        $this->repository->set('locked', 'bar');
        $this->assertSame('foo', $this->repository->get('locked'));
    }

    public function testSetLock()
    {
        $this->repository->set('will_lock', 'bar');
        $this->assertSame('bar', $this->repository->get('will_lock'));

        $this->repository->lock('will_lock');
        $this->repository->set('will_lock', 'foo');
        $this->assertSame('bar', $this->repository->get('will_lock'));
    }

    public function testSetUnlock()
    {
        $this->repository->unlock('locked');
        $this->repository->set('locked', 'bar');
        $this->assertSame('bar', $this->repository->get('locked'));
    }

    public function testRemove()
    {
        $this->repository->set('will_remove', 'bar');
        $this->assertSame('bar', $this->repository->get('will_remove'));

        $this->repository->remove('will_remove');
        $this->assertNull($this->repository->get('will_remove'));
        $this->assertFalse($this->repository->has('will_remove'));
    }

    public function testAll()
    {
        $autoloadOptions = [
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'bat',
            'boolean' => true,
            'array' => ['xxx', 'yyy'],
            'associate' => ['x' => 'xxx', 'y' => 'yyy'],
            'locked' => 'foo',
        ];

        $this->assertSame($autoloadOptions, $this->repository->all());
    }

    public function testOffsetExists()
    {
        $this->assertTrue(isset($this->repository['foo']));
        $this->assertFalse(isset($this->repository['not-exist']));
    }

    public function testOffsetGet()
    {
        $this->assertNull($this->repository['not-exist']);
        $this->assertSame('bar', $this->repository['foo']);
        $this->assertSame([
            'x' => 'xxx',
            'y' => 'yyy',
        ], $this->repository['associate']);
    }

    public function testOffsetSet()
    {
        $this->assertNull($this->repository['key']);

        $this->repository['key'] = 'value';

        $this->assertSame('value', $this->repository['key']);
    }

    public function testOffsetUnset()
    {
        $this->assertArrayHasKey('associate', $this->repository->all());
        $this->assertSame(['x' => 'xxx', 'y' => 'yyy'], $this->repository->get('associate'));

        unset($this->repository['associate']);

        $this->assertArrayHasKey('associate', $this->repository->all());
        $this->assertNull($this->repository->get('associate'));
    }

    public function testHelperInstance()
    {
        $this->assertInstanceOf(Repository::class, option());
    }

    public function testHelperGet()
    {
        $this->assertSame('bar', option('foo'));
        $this->assertInstanceOf(Repository::class, option());
    }

    public function testHelperSet()
    {
        $this->repository->set(['key' => 'value']);
        $this->assertSame('value', $this->repository->get('key'));
    }

    public function testSave()
    {
        $this->repository->set(['key' => 'value']);

        $this->assertDatabaseMissing('options', ['name' => 'key']);

        $this->repository->save();

        $this->assertDatabaseHas('options', ['name' => 'key']);

        $this->repository->remove(['key']);

        $this->assertDatabaseHas('options', ['name' => 'foo']);

        $this->assertDatabaseHas('options', ['name' => 'no_autoload']);
    }

    public function testEagerLoad()
    {
        $this->repository->setEagerLoad(false);

        $this->assertSame(null, $this->repository->get('no_autoload'));

        $this->repository->setEagerLoad(true);

        $this->assertSame('foo', $this->repository->get('no_autoload'));
    }

    public function testOnlyAutoload()
    {
        $this->repository->setOnlyAutoload(false);

        $this->repository->reload();

        $this->assertTrue($this->repository->has('no_autoload'));

        $this->repository->setOnlyAutoload(true);

        $this->repository->reload();

        $this->assertFalse($this->repository->has('no_autoload'));
    }
}
