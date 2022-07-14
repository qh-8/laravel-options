# Laravel Options

[![Latest Version on Packagist](https://img.shields.io/packagist/v/qh-8/laravel-options.svg?style=flat-square)](https://packagist.org/packages/qh-8/laravel-options)
[![Total Downloads](https://img.shields.io/packagist/dt/qh-8/laravel-options.svg?style=flat-square)](https://packagist.org/packages/qh-8/laravel-options)
![GitHub Actions](https://github.com/qh-8/laravel-options/actions/workflows/run-tests.yml/badge.svg)

This package allows you to store options in a repository (database).

## Installation

You can install the package via composer:

```bash
composer require qh-8/laravel-options
```

## Usage

Publish the migration files

```bash
php artisan vendor:publish --tag=option-migrations
```

Run migrate to create `options` table

```bash
php artisan migrate
```

Basic usage

```php
// via helper
option('key', 'default'); // get an option with default value.
option(['key' => 'default']); // set options.

// via service
$this->app['options']->has('key'); // check option exists.
$this->app['options']->get('key', 'default'); // get an option with default.
$this->app['options']->set('key', 'value'); // set an option.
$this->app['options']->set(['key' => 'value']); // set many options.
$this->app['options']->setMany(['key' => 'value']); // set many options.
$this->app['options']->lock('key'); // lock an option by key.
$this->app['options']->unlock('key'); // unlock an option by key.
$this->app['options']->remove('key'); // delete an option by key.
$this->app['options']->all(); // get all autoload options.
$this->app['options']->toArray(); // same all().
$this->app['options']->toJSon(); // same all() but json format.
$this->app['options']['key'] // array access: offsetGet
$this->app['options']['key'] = 'value' // array access: offsetSet
isset($this->app['options']['key']) // array access: offsetExists
unset($this->app['options']['key']) // array access: offsetUnset

// via facade
\Option::get('key');
\Qh\LaravelOptions\Facades\Option::get('key');

```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email dqh@dinhquochan.com instead of using the issue tracker.

## Credits

-   [Dinh Quoc Han](https://github.com/dinhquochan)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
