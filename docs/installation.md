---
title: Installation
description: Install Filterable, register its service provider, and prepare your Laravel application for its first filter.
tags: [installation, setup, requirements]
---

# Installation

## Requirements

- PHP 8.2 or later
- Laravel 10, 11, or 12

## Install the package

```bash
composer require kettasoft/filterable
```

## Register the service provider

Register `FilterableServiceProvider` before running the package commands.

For Laravel 11 and 12, add it to `bootstrap/providers.php`:

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

Continue with [How Filterable Works](/how-it-works), or go directly to the [Invokable Engine](/engines/invokable/) to define filter methods.
