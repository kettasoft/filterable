# An easy to use powerful Elqouent Filter

[![Total Downloads](https://img.shields.io/packagist/dt/kettasoft/laravel-eloquent-filter?style=flat-square)](https://packagist.org/packages/spatie/laravel-server-monitor)

## Installation

You can install this package via composer using this command:

```bash
composer require kettasoft/laravel-eloquent-filter
```

In Laravel 5.5 the service provider will automatically get registered. In older versions of the framework, you must install the service provider:

```php
// config/app.php
'providers' => [
    ...
    Kettasoft\EloquentFilter\ServiceProvider::class,
];
```

You can publish the config with:
```bash
php artisan vendor:publish --provider="Kettasoft\EloquentFilter\Providers\ServiceProvider" --tag="config"
```

You can make a new filter class with:
```bash
php artisan kettasoft:make-filter YourFilterName
```

You must publish the Stubs with:
```bash
php artisan vendor:publish --provider="Kettasoft\EloquentFilter\Providers\ServiceProvider" --tag="stubs"
```
