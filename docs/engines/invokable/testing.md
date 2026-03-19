---
title: Testing Filters
description: Learn how to test Invokable Engine filter classes in Laravel using PHPUnit or Pest. Covers basic filter assertions, annotation behavior, skipped filters, and strict mode exceptions.
tags: [testing, invokable-engine, phpunit, pest]
---

Testing filter classes in Filterable follows the same patterns as testing any Laravel
service — you simulate a request, apply the filter, and assert the resulting query or
response.

## Setup

Use `orchestra/testbench` for package-level tests, or Laravel's built-in testing tools
for application-level tests. No special configuration is needed for Filterable.

```php
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Tests\TestCase;

class PostFilterTest extends TestCase
{
    protected function makeRequest(array $params): Request
    {
        return Request::create('/posts', 'GET', $params);
    }
}
```

---

## Basic Filter Test

Assert that a filter method correctly modifies the query:

```php
public function test_status_filter_applies_where_clause(): void
{
    $request = $this->makeRequest(['status' => 'published']);

    $query = Post::filter(PostFilter::class, $request);

    $this->assertStringContainsString(
        'where "status" = ?',
        $query->toSql()
    );

    $this->assertContains('published', $query->getBindings());
}
```

---

## Testing with `getBindings()`

For precise assertions on both the SQL structure and the bound values:

```php
public function test_title_filter_uses_like_operator(): void
{
    $request = $this->makeRequest(['title' => 'laravel']);

    $query = Post::filter(PostFilter::class, $request);

    $this->assertStringContainsString('like', $query->toSql());
    $this->assertContains('%laravel%', $query->getBindings());
}
```

---

## Testing Skipped Filters

When a filter is skipped (e.g. via `#[SkipIf]`, `#[In]`, or `#[Authorize]`),
the clause should not appear in the query:

```php
public function test_status_filter_is_skipped_when_value_is_invalid(): void
{
    $request = $this->makeRequest(['status' => 'invalid_status']);

    $query = Post::filter(PostFilter::class, $request);

    $this->assertStringNotContainsString('status', $query->toSql());
}
```

---

## Testing `#[Required]` (Strict Mode)

`#[Required]` throws a `StrictnessException` instead of silently skipping.
Assert the exception is thrown when the value is empty:

```php
use Kettasoft\Filterable\Engines\Exceptions\StrictnessException;

public function test_required_annotation_throws_when_value_is_empty(): void
{
    $this->expectException(StrictnessException::class);

    $request = $this->makeRequest(['status' => '']);

    Post::filter(PostFilter::class, $request)->get();
}
```

---

## Testing `#[Authorize]`

Mock the authorizable class to control the authorization result:

```php
public function test_authorized_filter_executes(): void
{
    // Act as admin
    $this->actingAs(User::factory()->admin()->create());

    $request = $this->makeRequest(['secret_field' => 'value']);

    $query = Post::filter(PostFilter::class, $request);

    $this->assertStringContainsString('secret_field', $query->toSql());
}

public function test_unauthorized_filter_is_skipped(): void
{
    // Act as regular user
    $this->actingAs(User::factory()->create());

    $request = $this->makeRequest(['secret_field' => 'value']);

    $query = Post::filter(PostFilter::class, $request);

    $this->assertStringNotContainsString('secret_field', $query->toSql());
}
```

---

## Testing with Pest

The same assertions work with Pest syntax:

```php
it('applies status filter correctly', function () {
    $request = Request::create('/posts', 'GET', ['status' => 'published']);

    $query = Post::filter(PostFilter::class, $request);

    expect($query->toSql())->toContain('where "status" = ?');
    expect($query->getBindings())->toContain('published');
});

it('skips filter when value is not in allowed set', function () {
    $request = Request::create('/posts', 'GET', ['status' => 'unknown']);

    $query = Post::filter(PostFilter::class, $request);

    expect($query->toSql())->not->toContain('status');
});
```

---

## Testing Multiple Filters

Assert that combining multiple filters produces the correct compound query:

```php
public function test_multiple_filters_are_combined(): void
{
    $request = $this->makeRequest([
        'status' => 'published',
        'title'  => 'laravel',
    ]);

    $query = Post::filter(PostFilter::class, $request);
    $sql   = $query->toSql();

    $this->assertStringContainsString('status', $sql);
    $this->assertStringContainsString('title', $sql);
}
```

---

## Tips

- Use `->toSql()` and `->getBindings()` instead of running actual DB queries in unit tests.
- Test each annotation's behavior in isolation using a minimal filter class with only that annotation.
- For `#[Authorize]`, prefer `actingAs()` over mocking the authorizable class directly — it's closer to real behavior.
- Keep one filter class per test file to avoid interference between tests.
