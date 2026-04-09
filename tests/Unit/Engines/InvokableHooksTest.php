<?php

namespace Kettasoft\Filterable\Tests\Unit\Engines;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;

/**
 * Tests for the Invokable engine per-field lifecycle hooks system.
 *
 * Hook execution order per field:
 *   onEmpty{Field}(payload)         ← only when value is null/''
 *   before{Field}(payload)          ← field-level; false = skip method
 *   → applyFilterMethod() OR onSkip{Field}(payload)
 *   after{Field}(payload)           ← field-level after
 */
class InvokableHooksTest extends TestCase
{
  use RefreshDatabase;

  public function setUp(): void
  {
    parent::setUp();

    Post::factory(3)->create(['status' => 'active', 'title' => 'Active Post']);
    Post::factory(2)->create(['status' => 'pending', 'title' => 'Pending Post']);
  }

  // =========================================================================
  //  before{Field} — field-level before hook
  // =========================================================================

  public function test_field_before_hook_fires_before_field_method()
  {
    $order = [];

    request()->merge(['status' => 'active']);

    $filter = new class($order) extends Filterable {
      public function __construct(private array &$log)
      {
        parent::__construct();
      }
      protected $filters = ['status'];

      public function beforeStatus(Payload $payload): void
      {
        $this->log[] = 'before';
      }

      public function status(Payload $payload): void
      {
        $this->log[] = 'method';
        $this->builder->where('status', $payload->value);
      }
    };

    Post::filter($filter)->get();

    $this->assertEquals(['before', 'method'], array_values(array_filter($order, fn($v) => in_array($v, ['before', 'method']))));
  }

  public function test_field_before_hook_returning_false_skips_filter_method()
  {
    request()->merge(['status' => 'active']);

    $filter = new class extends Filterable {
      protected $filters = ['status'];
      public bool $methodCalled = false;

      public function beforeStatus(Payload $payload): bool
      {
        return false;
      }

      public function status(Payload $payload): void
      {
        $this->methodCalled = true;
        $this->builder->where('status', $payload->value);
      }
    };

    $query = Post::filter($filter);

    $this->assertFalse($filter->methodCalled);
    $this->assertStringNotContainsString('"status" =', $query->toSql());
  }

  public function test_field_before_hook_returning_true_allows_method_to_run()
  {
    request()->merge(['status' => 'active']);

    $filter = new class extends Filterable {
      protected $filters = ['status'];
      public bool $methodCalled = false;

      public function beforeStatus(Payload $payload): bool
      {
        return true;
      }

      public function status(Payload $payload): void
      {
        $this->methodCalled = true;
        $this->builder->where('status', $payload->value);
      }
    };

    Post::filter($filter)->get();

    $this->assertTrue($filter->methodCalled);
  }

  // =========================================================================
  //  after{Field} — field-level after hook
  // =========================================================================

  public function test_field_after_hook_fires_after_filter_method()
  {
    $order = [];

    request()->merge(['status' => 'active']);

    $filter = new class($order) extends Filterable {
      public function __construct(private array &$log)
      {
        parent::__construct();
      }
      protected $filters = ['status'];

      public function status(Payload $payload): void
      {
        $this->log[] = 'method';
        $this->builder->where('status', $payload->value);
      }

      public function afterStatus(Payload $payload): void
      {
        $this->log[] = 'after';
      }
    };

    Post::filter($filter)->get();

    $this->assertContains('method', $order);
    $this->assertContains('after', $order);

    $methodPos = array_search('method', $order);
    $afterPos  = array_search('after', $order);
    $this->assertGreaterThan($methodPos, $afterPos);
  }

  public function test_field_after_hook_does_not_fire_when_before_halted()
  {
    $calls = [];

    request()->merge(['status' => 'active']);

    $filter = new class($calls) extends Filterable {
      public function __construct(private array &$log)
      {
        parent::__construct();
      }
      protected $filters = ['status'];

      public function beforeStatus(Payload $payload): bool
      {
        return false;
      }

      public function status(Payload $payload): void
      {
        $this->builder->where('status', $payload->value);
      }

      public function afterStatus(Payload $payload): void
      {
        $this->log[] = 'afterStatus';
      }
    };

    Post::filter($filter)->get();

    $this->assertNotContains('afterStatus', $calls);
  }

  // =========================================================================
  //  onSkip{Field} — skip hook
  // =========================================================================

  public function test_skip_hook_fires_when_filter_method_is_not_defined()
  {
    $calls = [];

    request()->merge(['title' => 'hello']);

    $filter = new class($calls) extends Filterable {
      public function __construct(private array &$log)
      {
        parent::__construct();
      }
      protected $filters = ['title'];

      public function onSkipTitle(Payload $payload): void
      {
        $this->log[] = 'onSkipTitle';
      }
      // no title() method defined intentionally
    };

    Post::filter($filter)->get();

    $this->assertContains('onSkipTitle', $calls);
  }

  public function test_skip_hook_does_not_fire_when_filter_method_exists()
  {
    $calls = [];

    request()->merge(['title' => 'hello']);

    $filter = new class($calls) extends Filterable {
      public function __construct(private array &$log)
      {
        parent::__construct();
      }
      protected $filters = ['title'];

      public function onSkipTitle(Payload $payload): void
      {
        $this->log[] = 'onSkipTitle';
      }

      public function title(Payload $payload): void
      {
        $this->builder->where('title', $payload->value);
      }
    };

    Post::filter($filter)->get();

    $this->assertNotContains('onSkipTitle', $calls);
  }

  // =========================================================================
  //  onEmpty{Field} — empty hook
  // =========================================================================

  public function test_empty_hook_fires_when_value_is_empty_string()
  {
    $calls = [];

    request()->merge(['status' => '']);

    $filter = new class($calls) extends Filterable {
      public function __construct(private array &$log)
      {
        parent::__construct();
      }
      protected $filters = ['status'];

      public function onEmptyStatus(Payload $payload): void
      {
        $this->log[] = 'onEmptyStatus';
      }

      public function status(Payload $payload): void
      {
        $this->builder->where('status', $payload->value);
      }
    };

    Post::filter($filter)->get();

    $this->assertContains('onEmptyStatus', $calls);
  }

  public function test_empty_hook_fires_when_value_is_null()
  {
    $calls = [];

    request()->merge(['status' => null]);

    $filter = new class($calls) extends Filterable {
      public function __construct(private array &$log)
      {
        parent::__construct();
      }
      protected $filters = ['status'];

      public function onEmptyStatus(Payload $payload): void
      {
        $this->log[] = 'onEmptyStatus';
      }

      public function status(Payload $payload): void
      {
        $this->builder->where('status', $payload->value);
      }
    };

    Post::filter($filter)->get();

    $this->assertContains('onEmptyStatus', $calls);
  }

  public function test_empty_hook_does_not_fire_when_value_is_present()
  {
    $calls = [];

    request()->merge(['status' => 'active']);

    $filter = new class($calls) extends Filterable {
      public function __construct(private array &$log)
      {
        parent::__construct();
      }
      protected $filters = ['status'];

      public function onEmptyStatus(Payload $payload): void
      {
        $this->log[] = 'onEmptyStatus';
      }

      public function status(Payload $payload): void
      {
        $this->builder->where('status', $payload->value);
      }
    };

    Post::filter($filter)->get();

    $this->assertNotContains('onEmptyStatus', $calls);
  }

  // =========================================================================
  //  hooks disabled globally
  // =========================================================================

  public function test_no_hooks_fire_when_enabled_is_false()
  {
    $calls = [];

    config(['filterable.engines.invokable.hooks.enabled' => false]);

    request()->merge(['status' => 'active']);

    $filter = new class($calls) extends Filterable {
      public function __construct(private array &$log)
      {
        parent::__construct();
      }
      protected $filters = ['status'];

      public function beforeStatus(Payload $payload): void
      {
        $this->log[] = 'beforeStatus';
      }

      public function status(Payload $payload): void
      {
        $this->builder->where('status', $payload->value);
      }

      public function afterStatus(Payload $payload): void
      {
        $this->log[] = 'afterStatus';
      }
    };

    Post::filter($filter)->get();

    $this->assertEmpty($calls, 'No hooks should fire when enabled = false');

    config(['filterable.engines.invokable.hooks.enabled' => true]);
  }

  // =========================================================================
  //  field_hooks disabled
  // =========================================================================

  public function test_field_hooks_do_not_fire_when_field_hooks_disabled()
  {
    $calls = [];

    config(['filterable.engines.invokable.hooks.field_hooks' => false]);

    request()->merge(['status' => 'active']);

    $filter = new class($calls) extends Filterable {
      public function __construct(private array &$log)
      {
        parent::__construct();
      }
      protected $filters = ['status'];

      public function beforeStatus(Payload $payload): void
      {
        $this->log[] = 'beforeStatus';
      }

      public function status(Payload $payload): void
      {
        $this->builder->where('status', $payload->value);
      }

      public function afterStatus(Payload $payload): void
      {
        $this->log[] = 'afterStatus';
      }
    };

    Post::filter($filter)->get();

    $this->assertNotContains('beforeStatus', $calls);
    $this->assertNotContains('afterStatus', $calls);

    config(['filterable.engines.invokable.hooks.field_hooks' => true]);
  }

  // =========================================================================
  //  halt_on_false = false
  // =========================================================================

  public function test_before_hook_false_does_not_halt_when_halt_on_false_disabled()
  {
    config(['filterable.engines.invokable.hooks.halt_on_false' => false]);

    request()->merge(['status' => 'active']);

    $filter = new class extends Filterable {
      protected $filters = ['status'];
      public bool $methodCalled = false;

      public function beforeStatus(Payload $payload): bool
      {
        return false;
      }

      public function status(Payload $payload): void
      {
        $this->methodCalled = true;
        $this->builder->where('status', $payload->value);
      }
    };

    Post::filter($filter)->get();

    $this->assertTrue($filter->methodCalled, 'Method should run when halt_on_false = false');

    config(['filterable.engines.invokable.hooks.halt_on_false' => true]);
  }

  // =========================================================================
  //  naming conventions
  // =========================================================================

  public function test_camel_naming_convention_for_underscore_field()
  {
    $calls = [];

    config(['filterable.engines.invokable.hooks.naming' => 'camel']);

    request()->merge(['created_at' => '2024-01-01']);

    $filter = new class($calls) extends Filterable {
      public function __construct(private array &$log)
      {
        parent::__construct();
      }
      protected $filters = ['created_at'];

      // camel: beforeCreatedAt
      public function beforeCreatedAt(Payload $payload): void
      {
        $this->log[] = 'beforeCreatedAt';
      }

      public function createdAt(Payload $payload): void
      {
        $this->builder->where('created_at', $payload->value);
      }
    };

    Post::filter($filter)->get();

    $this->assertContains('beforeCreatedAt', $calls);

    config(['filterable.engines.invokable.hooks.naming' => 'camel']);
  }

  public function test_studly_naming_convention_for_underscore_field()
  {
    $calls = [];

    config(['filterable.engines.invokable.hooks.naming' => 'studly']);

    request()->merge(['created_at' => '2024-01-01']);

    $filter = new class($calls) extends Filterable {
      public function __construct(private array &$log)
      {
        parent::__construct();
      }
      protected $filters = ['created_at'];

      // studly: beforeCreatedAt
      public function beforeCreatedAt(Payload $payload): void
      {
        $this->log[] = 'beforeCreatedAt';
      }

      public function createdAt(Payload $payload): void
      {
        $this->builder->where('created_at', $payload->value);
      }
    };

    Post::filter($filter)->get();

    $this->assertContains('beforeCreatedAt', $calls);

    config(['filterable.engines.invokable.hooks.naming' => 'camel']);
  }

  // =========================================================================
  //  Custom prefix
  // =========================================================================

  public function test_custom_prefix_resolves_correct_method_name()
  {
    $calls = [];

    config(['filterable.engines.invokable.hooks.prefix' => [
      'before' => 'hookBefore',
      'after'  => 'hookAfter',
      'skip'   => 'onSkip',
      'empty'  => 'onEmpty',
    ]]);

    request()->merge(['status' => 'active']);

    $filter = new class($calls) extends Filterable {
      public function __construct(private array &$log)
      {
        parent::__construct();
      }
      protected $filters = ['status'];

      public function hookBeforeStatus(Payload $payload): void
      {
        $this->log[] = 'hookBeforeStatus';
      }

      public function status(Payload $payload): void
      {
        $this->builder->where('status', $payload->value);
      }

      public function hookAfterStatus(Payload $payload): void
      {
        $this->log[] = 'hookAfterStatus';
      }
    };

    Post::filter($filter)->get();

    $this->assertContains('hookBeforeStatus', $calls);
    $this->assertContains('hookAfterStatus', $calls);

    // Reset to default
    config(['filterable.engines.invokable.hooks.prefix' => [
      'before' => 'before',
      'after'  => 'after',
      'skip'   => 'onSkip',
      'empty'  => 'onEmpty',
    ]]);
  }

  // =========================================================================
  //  Full hook lifecycle order
  // =========================================================================

  public function test_full_lifecycle_order_is_correct()
  {
    $order = [];

    request()->merge(['status' => 'active']);

    $filter = new class($order) extends Filterable {

      public function __construct(private array &$log)
      {
        parent::__construct();
      }

      protected $filters = ['status'];

      public function initially(Builder $builder): Builder
      {
        $this->log[] = 'initially';
        return $builder;
      }

      protected function finally(Builder $builder): Builder
      {
        $this->log[] = 'finally';
        return $builder;
      }

      public function beforeStatus(Payload $payload): void
      {
        $this->log[] = 'beforeStatus';
      }

      public function status(Payload $payload): void
      {
        $this->log[] = 'status';
      }

      public function afterStatus(Payload $payload): void
      {
        $this->log[] = 'afterStatus';
      }
    };

    Post::filter($filter)->get();

    $this->assertEquals(
      ['initially', 'beforeStatus', 'status', 'afterStatus', 'finally'],
      $order,
      'Hook lifecycle order must be initially → beforeStatus → status → afterStatus → finally'
    );
  }
}
