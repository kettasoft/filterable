<p align="center"><img src="https://github.com/kettasoft/filterable/blob/docs/images/logo.png" width="180" alt="Filterable Logo" /></p>
<h1 align="center">Filterable</h1>
<p align="center">A powerful and flexible Laravel package for advanced, clean, and scalable filtering of Eloquent models using multiple customizable engines.</p><p align="center">
<a href="https://packagist.org/packages/kettasoft/filterable"><img src="https://github.com/kettasoft/filterable/actions/workflows/php.yml/badge.svg?style=flat-square" alt="Tests"></a>
<a href="https://packagist.org/packages/kettasoft/filterable"><img src="http://poser.pugx.org/kettasoft/filterable/v?style=flat-square" alt="Latest Version on Packagist"></a>
<a href="https://packagist.org/packages/kettasoft/filterable"><img src="https://img.shields.io/packagist/dt/kettasoft/filterable?style=flat-square" alt="Total Downloads"></a>
<a href="https://github.com/kettasoft/filterable/blob/master/LICENSE"><img src="https://poser.pugx.org/kettasoft/filterable/license?style=flat-square" alt="License"></a></p>

## ✨ Overview

**Filterable** lets you build highly customizable filtering logic for Laravel's Eloquent queries without messy conditions. With support for multiple engines like:

- Ruleset Engine
- Invokable Engine
- Expression Engine
- Tree Engine

...you can structure your filter logic however you like — from simple lists to deeply nested conditional trees with relationship support.

## ⚙️ Key Features

- **Multiple Filtering Engines**
- **Chainable & Nested Filter Logic**
- **Relation & Nested Relation Filtering**
- **Custom Operators & Sanitization**
- **SOLID & Extensible Design**
- **Zero-Config Optional Defaults**


## 📚 Documentation

For full documentation, installation, and usage examples, visit: **[https://kettasoft.github.io/filterable](https://kettasoft.github.io/filterable)**

---

## ✅ Quick Start

```bash
composer require kettasoft/filterable
```

Use it in your controller:

```php
$posts = Post::filter(new PostFilter)->paginate();
```

Create your PostFilter using your preferred engine.

License

MIT © 2024-present Kettasoft
