---
title: Installation Guide
description: Learn how to install the Filterable Laravel package via Composer,
    register the service provider, publish config files, and create your first
    filter class — fully set up in minutes.
tags: [installation, setup, getting started, requirements]
---

## Requirements

- PHP **8.+**
- Laravel **10.x** or higher

## Installation

Install the package via Composer:

```bash
composer require kettasoft/filterable
```

## Setup

### Step 1: Register the service provider

> **Note:** Laravel 11+ registers providers automatically via package discovery.
> Skip this step if you're on Laravel 11+.

Add the service provider to the `providers` array in `config/app.php`:

```php
'providers' => [
    // ...
    Kettasoft\Filterable\Providers\FilterableServiceProvider::class,
],
```

### Step 2: Publish configuration and stubs

Publish the config file:

```bash
php artisan vendor:publish \
  --provider="Kettasoft\Filterable\Providers\FilterableServiceProvider" \
  --tag="config"
```

Publish the stubs:

```bash
php artisan vendor:publish \
  --provider="Kettasoft\Filterable\Providers\FilterableServiceProvider" \
  --tag="stubs"
```

### Step 3: Add the `Filterable` trait to your model

Include the `Filterable` trait in any Eloquent model you want to filter:

```php
<?php

use Kettasoft\Filterable\Filterable;

class Post extends Model
{
    use Filterable;
}
```

### Step 4: Create a filter class

Generate a filter class for your model using the Artisan command:

```bash
php artisan kettasoft:make-filter PostFilter --filters=title,status
```

This generates a dedicated filter class where you define your filter methods.

---

Next, learn how to [define filter methods](/usage/defining-filters)
and apply them to your queries.
