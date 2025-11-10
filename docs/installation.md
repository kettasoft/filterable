# 📦 Installation

To install **Filterable**, simply use Composer to add it to your project:

```bash
composer require kettasoft/filterable
```

### **Service Provider Registration**

Add the following line to the **`providers`** array in **`config/app.php`**:

```php
'providers' => [

    ...

    Kettasoft\Filterable\Providers\FilterableServiceProvider::class,

];
```

### **Publishing Configuration and Stubs**

After installation, you can publish the configuration file and stubs with the following commands:

```bash
php artisan vendor:publish --provider="Kettasoft\Filterable\Providers\FilterableServiceProvider" --tag="config"
php artisan vendor:publish --provider="Kettasoft\Filterable\Providers\FilterableServiceProvider" --tag="stubs"
```

---

### **Step 1: Add the `Filterable` Trait to Your Model**

To enable filtering on your model, you need to include the `Filterable` trait in the model you want to apply filters on.

```php
<?php

use Kettasoft\Filterable\Filterable;

class Post extends Model
{
    use Filterable;
}
```

---

### **Step 2: Create a Custom Filter Class**

You can generate a custom filter class for your model by running the artisan command:

```bash
php artisan kettasoft:make-filter PostFilter --filters=title,status
```

This command will generate a filter class where you can define custom filter methods.

---
