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

Publish the migration file

```bash
php artisan vendor:publish --tag=option-migrations
```

Publish the config file

```bash
php artisan vendor:publish --tag=option-config
```

Create the `options` table via artisan migrate command

```bash
php artisan migrate
```

Basic usage

```php
// Via helper.
option('key', 'default'); // Get an option with default value.
option(['key' => 'default']); // Set options.
option()->save(); // Delete, update or insert to database.

// Via service.
$this->app['options']->has('key'); // Check option exists.
$this->app['options']->get('key', 'default'); // Get an option with default.
$this->app['options']->set('key', 'value'); // Set an option.
$this->app['options']->set(['key' => 'value']); // Set many options.
$this->app['options']->setMany(['key' => 'value']); // Set many options.
$this->app['options']->lock('key'); // Lock an option by key.
$this->app['options']->unlock('key'); // Unlock an option by key.
$this->app['options']->remove('key'); // Delete an option by key.
$this->app['options']->all(); // Get all autoload options.
$this->app['options']->toArray(); // Same all().
$this->app['options']->toJSon(); // Same all() but json format.
$this->app['options']['key'] // Array access: offsetGet
$this->app['options']['key'] = 'value' // Array access: offsetSet.
isset($this->app['options']['key']) // Array access: offsetExists.
unset($this->app['options']['key']) // Array access: offsetUnset.
```

### Configuration

```dotenv
# Enable autoload options only.
OPTIONS_ONLY_AUTOLOAD=true

# Eager load options.
OPTIONS_EAGER_LOAD=true
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
