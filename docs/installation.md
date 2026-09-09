---
title: Installation
description: Install Filterable, register its service provider, and prepare your Laravel application for its first filter.
tags: [installation, setup, requirements]
---

# Installation

## Requirements

- Laravel 10, 11, 12, or 13
- A PHP version supported by your Laravel release. Laravel 13 requires PHP 8.3 or later.

## Install the package

```bash
composer require kettasoft/filterable
```

## Register the service provider

Register `FilterableServiceProvider` before running the package commands.

For Laravel 11, 12, and 13, add it to `bootstrap/providers.php`:

```php
return [
    App\Providers\AppServiceProvider::class,
    Kettasoft\Filterable\Providers\FilterableServiceProvider::class,
];
```

For Laravel 10, add it to the `providers` array in `config/app.php`:

```php
'providers' => [
    // ...
    Kettasoft\Filterable\Providers\FilterableServiceProvider::class,
],
```

## Run the setup command

The setup command publishes `config/filterable.php` and creates the default filters directory:

```bash
php artisan filterable:setup
```

To publish individual resources instead:

```bash
php artisan vendor:publish --tag=filterable-config
php artisan vendor:publish --tag=filterable-stubs
```

## Create your first filter

```bash
php artisan filterable:make-filter PostFilter --filters=title,status
```

This creates `app/Http/Filters/PostFilter.php` by default. You can change the default path and namespace in `config/filterable.php`, or override them for one command:

```bash
php artisan filterable:make-filter PostFilter \
  --namespace="Modules\\Blog\\App\\Filters" \
  --path="Modules/Blog/app/Filters"
```

Continue with the [Quick Start](/quick-start) to build your first filtered endpoint. If you already know the package basics, use [Choose an Engine](/choosing-an-engine) to design your request contract.
