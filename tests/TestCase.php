<?php

namespace Qh\LaravelOptions\Tests;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function defineDatabaseMigrations(): void
    {
        $this->artisan('migrate', ['--database' => 'testing']);

        $class = include __DIR__ . '/../stubs/create_options_table.php.stub';
        $class->up();
    }

    protected function getPackageProviders($app)
    {
        return [
            \Qh\LaravelOptions\LaravelOptionsServiceProvider::class,
        ];
    }
}
