# Cache Scoping

::: tip What is Cache Scoping?
Cache scoping allows you to segment your cache by different contexts (users, tenants, organizations, etc.) to ensure that cached data is properly isolated and invalidated.
:::

## Overview

Without scoping, cached results might be shared across different users or tenants, leading to data leaks or incorrect results. Cache scoping solves this by adding contextual information to cache keys.

### Benefits

-   **Data Isolation**: Keep user and tenant data separate
-   **Precise Invalidation**: Clear cache for specific users/tenants only
-   **Multi-tenancy Support**: Essential for SaaS applications
-   **Security**: Prevent data leaks between contexts

## User Scoping

### Basic User Scoping

Automatically scope cache by the authenticated user:

```php
use App\Models\Post;

// Automatic user scoping
$posts = Post::filter()
    ->cache(3600)
    ->scopeByUser()  // Uses auth()->id()
    ->get();

// Each user gets their own cached result
// Cache keys:
// - filterable:post_filter:user:1:...
// - filterable:post_filter:user:2:...
```

### Explicit User ID

Specify a user ID explicitly:

```php
// Scope by specific user
$posts = Post::filter()
    ->cache(3600)
    ->scopeByUser(123)
    ->get();
```

### Use Cases

**Personalized Feeds**

```php
class FeedController
{
    public function index()
    {
        // Each user sees their own cached feed
        return Post::filter()
            ->cache(1800)
            ->scopeByUser()
            ->apply(['feed_type' => 'personalized'])
            ->get();
    }
}
```

**User Dashboards**

```php
class DashboardController
{
    public function show()
    {
        $stats = DashboardStats::filter()
            ->cache(600)
            ->scopeByUser()
            ->get();

        return view('dashboard', compact('stats'));
    }
}
```

**User Preferences**

```php
// Cache user-specific filtered results
$products = Product::filter()
    ->cache(3600)
    ->scopeByUser()
    ->apply([
        'category' => auth()->user()->favorite_category,
        'price_range' => auth()->user()->price_preference,
    ])
    ->get();
```

## Tenant Scoping

### Basic Tenant Scoping

Essential for multi-tenant applications:

```php
use App\Models\Product;

// Scope by current tenant
$products = Product::filter()
    ->cache(3600)
    ->scopeByTenant(tenant()->id)
    ->get();

// Cache keys:
// - filterable:product_filter:tenant:acme:...
// - filterable:product_filter:tenant:globex:...
```

### Integration with Tenant Packages

#### Stancl/Tenancy

```php
use Stancl\Tenancy\Facades\Tenancy;

$orders = Order::filter()
    ->cache(3600)
    ->scopeByTenant(tenant('id'))
    ->apply(['status' => 'pending'])
    ->get();
```

#### Spatie Multi-Tenancy

```php
use Spatie\Multitenancy\Models\Tenant;

$data = Model::filter()
    ->cache(3600)
    ->scopeByTenant(Tenant::current()->id)
    ->get();
```

### Use Cases

**Tenant Dashboards**

```php
class TenantDashboardController
{
    public function index()
    {
        $metrics = Metric::filter()
            ->cache(600)
            ->scopeByTenant(tenant()->id)
            ->get();

        return view('tenant.dashboard', compact('metrics'));
    }
}
```

**Tenant Reports**

```php
// Each tenant has their own cached reports
$report = SalesReport::filter()
    ->cache(7200)
    ->scopeByTenant(tenant()->id)
    ->apply(['year' => 2024])
    ->get();
```

**Tenant Settings**

```php
// Cache tenant-specific configuration
$settings = Setting::filter()
    ->cacheForever()
    ->scopeByTenant(tenant()->id)
    ->get();
```

## Custom Scoping

### Single Custom Scope

Add any custom scope to your cache:

```php
use App\Models\Document;

// Scope by organization
$documents = Document::filter()
    ->cache(3600)
    ->scopeBy('organization', $organizationId)
    ->get();

// Scope by department
$reports = Report::filter()
    ->cache(3600)
    ->scopeBy('department', $departmentId)
    ->get();
```

### Multiple Custom Scopes

Chain multiple scopes for fine-grained control:

```php
// Scope by organization and department
$data = Model::filter()
    ->cache(3600)
    ->scopeBy('organization', $orgId)
    ->scopeBy('department', $deptId)
    ->scopeBy('region', $region)
    ->get();

// Cache key includes all scopes:
// filterable:model_filter:organization:123:department:456:region:west:...
```

### Batch Scopes

Set multiple scopes at once:

```php
$filters = Request::filter()
    ->cache(3600)
    ->withScopes([
        'organization' => $orgId,
        'department' => $deptId,
        'region' => $region,
        'team' => $teamId,
    ])
    ->get();
```

### Use Cases

**Multi-Level Organizations**

```php
class OrganizationController
{
    public function departmentData($orgId, $deptId)
    {
        return Data::filter()
            ->cache(1800)
            ->scopeBy('organization', $orgId)
            ->scopeBy('department', $deptId)
            ->get();
    }
}
```

**Geographic Segmentation**

```php
// Different cache for each region
$products = Product::filter()
    ->cache(3600)
    ->scopeBy('country', $country)
    ->scopeBy('region', $region)
    ->apply(['status' => 'active'])
    ->get();
```

**Temporal Scoping**

```php
// Different cache for different time periods
$analytics = Analytics::filter()
    ->cache(3600)
    ->scopeBy('period', 'monthly')
    ->scopeBy('year', 2024)
    ->scopeBy('month', 3)
    ->get();
```

## Combined Scoping

### User + Tenant

Perfect for SaaS applications where users belong to tenants:

```php
// Scope by both tenant and user
$personalData = UserData::filter()
    ->cache(1800)
    ->scopeByTenant(tenant()->id)
    ->scopeByUser()
    ->get();

// Cache key:
// filterable:user_data_filter:tenant:acme:user:123:...
```

### User + Custom Scopes

```php
// User-specific data within an organization
$projects = Project::filter()
    ->cache(3600)
    ->scopeByUser()
    ->scopeBy('organization', $orgId)
    ->scopeBy('team', $teamId)
    ->get();
```

### Multiple Context Layers

```php
class MultiContextFilter
{
    public function getData($userId, $tenantId, $orgId, $role)
    {
        return Model::filter()
            ->cache(3600)
            ->scopeByUser($userId)
            ->scopeByTenant($tenantId)
            ->scopeBy('organization', $orgId)
            ->scopeBy('role', $role)
            ->get();
    }
}
```

## Scope Patterns

### Middleware-based Scoping

Automatically apply scopes via middleware:

```php
// app/Http/Middleware/ApplyCacheScopes.php
class ApplyCacheScopes
{
    public function handle($request, Closure $next)
    {
        // Store scopes in a service
        app(FilterableCacheManager::class)
            ->addScope('tenant', tenant()->id)
            ->addScope('user', auth()->id());

        return $next($request);
    }
}
```

```php
// Use in routes
Route::middleware(['auth', 'tenant', ApplyCacheScopes::class])
    ->group(function () {
        // All filterable queries here will be scoped automatically
    });
```

### Base Filter with Default Scoping

```php
abstract class TenantScopedFilter extends Filterable
{
    use HasFilterableCache;

    protected function applyCache(int $ttl = 3600)
    {
        return $this->cache($ttl)
            ->scopeByTenant(tenant()->id);
    }
}
```

```php
// Usage
class ProductFilter extends TenantScopedFilter
{
    public function apply(array $filters = []): Builder
    {
        return parent::apply($filters)
            ->applyCache();  // Automatically tenant-scoped
    }
}
```

### Service Provider Integration

```php
// app/Providers/FilterableCacheServiceProvider.php
class FilterableCacheServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Auto-apply tenant scope to all filterable caches
        Filterable::resolving(function ($filter) {
            if (auth()->check() && tenant()) {
                $filter->cache(3600)
                    ->scopeByTenant(tenant()->id)
                    ->scopeByUser();
            }
        });
    }
}
```

## Cache Invalidation with Scopes

### Flush User-specific Cache

```php
use Kettasoft\Filterable\Caching\FilterableCacheManager;

// Clear all cache for a specific user
$manager = FilterableCacheManager::getInstance();
$manager->addScope('user', $userId)
    ->flushByTags(['posts']);
```

### Flush Tenant-specific Cache

```php
// Clear all cache for a tenant
$manager = FilterableCacheManager::getInstance();
$manager->addScope('tenant', $tenantId)
    ->flushByTags(['products', 'orders']);
```

### Selective Invalidation

```php
// Clear specific scope combinations
function clearUserTenantCache($userId, $tenantId, array $tags)
{
    $manager = FilterableCacheManager::getInstance();
    $manager->addScope('user', $userId)
        ->addScope('tenant', $tenantId)
        ->flushByTags($tags);
}

clearUserTenantCache(123, 'acme', ['dashboard', 'reports']);
```

## Real-world Examples

### SaaS Platform

```php
// Multi-level scoping for SaaS
class SaaSDataController
{
    public function tenantUserData()
    {
        return Data::filter()
            ->cache(1800)
            ->scopeByTenant(tenant()->id)
            ->scopeByUser()
            ->scopeBy('subscription_tier', auth()->user()->tier)
            ->get();
    }

    public function organizationData($orgId)
    {
        return OrganizationData::filter()
            ->cache(3600)
            ->scopeByTenant(tenant()->id)
            ->scopeBy('organization', $orgId)
            ->get();
    }
}
```

### Multi-Organization Platform

```php
// User can belong to multiple organizations
class OrganizationDataController
{
    public function index($organizationId)
    {
        // Scope by current organization
        return Model::filter()
            ->cache(3600)
            ->scopeBy('organization', $organizationId)
            ->scopeByUser()  // User-specific within org
            ->get();
    }
}
```

### Regional E-commerce

```php
// Different pricing/products per region
class ProductController
{
    public function catalog()
    {
        $region = request()->header('X-Region', 'US');
        $currency = request()->header('X-Currency', 'USD');

        return Product::filter()
            ->cache(3600)
            ->scopeBy('region', $region)
            ->scopeBy('currency', $currency)
            ->scopeBy('language', app()->getLocale())
            ->apply(['status' => 'active'])
            ->get();
    }
}
```

## Advanced Patterns

### Dynamic Scope Resolution

```php
class SmartScopeFilter extends Filterable
{
    protected function resolveScopes(): array
    {
        $scopes = [];

        if (auth()->check()) {
            $scopes['user'] = auth()->id();
        }

        if (tenant()) {
            $scopes['tenant'] = tenant()->id;
        }

        if (session()->has('organization')) {
            $scopes['organization'] = session('organization');
        }

        return $scopes;
    }

    public function apply(array $filters = []): Builder
    {
        $query = parent::apply($filters)->cache(3600);

        foreach ($this->resolveScopes() as $key => $value) {
            $query->scopeBy($key, $value);
        }

        return $query;
    }
}
```

### Scope Inheritance

```php
// Base filter with default scopes
abstract class ScopedFilter extends Filterable
{
    protected array $defaultScopes = [];

    protected function getScopes(): array
    {
        return array_merge($this->defaultScopes, [
            'environment' => app()->environment(),
        ]);
    }

    protected function applyScopedCache(int $ttl = 3600)
    {
        $cache = $this->cache($ttl);

        foreach ($this->getScopes() as $key => $value) {
            $cache->scopeBy($key, $value);
        }

        return $cache;
    }
}
```

```php
// Specific filter inheriting scope behavior
class ProductFilter extends ScopedFilter
{
    protected array $defaultScopes = [
        'category' => 'default',
    ];
}
```

## Best Practices

### 1. Always Scope Multi-tenant Data

```php
// ❌ Bad - Shared cache across tenants
$data = Model::filter()->cache(3600)->get();

// ✅ Good - Tenant-isolated cache
$data = Model::filter()
    ->cache(3600)
    ->scopeByTenant(tenant()->id)
    ->get();
```

### 2. Scope Sensitive User Data

```php
// ❌ Bad - User A might see User B's cached data
$personalData = UserData::filter()->cache(1800)->get();

// ✅ Good - Each user has separate cache
$personalData = UserData::filter()
    ->cache(1800)
    ->scopeByUser()
    ->get();
```

### 3. Use Consistent Scope Keys

```php
// ❌ Bad - Inconsistent naming
->scopeBy('org', $id)
->scopeBy('organization', $id)
->scopeBy('org_id', $id)

// ✅ Good - Consistent across application
->scopeBy('organization', $id)
```

### 4. Document Your Scoping Strategy

```php
/**
 * ProductFilter
 *
 * Cache Scoping:
 * - tenant: Current tenant ID
 * - region: Geographic region
 * - currency: Currency code
 *
 * Example cache key:
 * filterable:product_filter:tenant:acme:region:US:currency:USD:...
 */
class ProductFilter extends Filterable
{
    // ...
}
```

### 5. Monitor Scope Effectiveness

```php
// Log scope usage for debugging
Log::debug('Cache scope applied', [
    'filter' => static::class,
    'scopes' => $this->getCacheScopes(),
    'key' => $this->getCacheKey(),
]);
```

## Troubleshooting

### Cache Leaking Between Users

```php
// Problem: Users seeing each other's data
// Solution: Add user scoping
->scopeByUser()
```

### Cache Not Invalidating

```php
// Problem: Cache persists after tenant data changes
// Solution: Ensure auto-invalidation observer includes tenant scope

// config/filterable.php
'auto_invalidate' => [
    'enabled' => true,
    'models' => [
        Product::class => function($product) {
            return [
                'products',
                "tenant:{$product->tenant_id}",
            ];
        },
    ],
],
```

### Inconsistent Cache Keys

```php
// Problem: Same query generating different keys
// Solution: Ensure scope order is consistent

// Use withScopes() for consistent ordering
->withScopes([
    'tenant' => $tenantId,
    'user' => $userId,
    'organization' => $orgId,
])
```

::: tip Next Steps

-   [Getting Started →](./getting-started.md)
-   [Auto-invalidation →](./auto-invalidation.md)
-   [Cache Profiles →](./profiles.md)
    :::
