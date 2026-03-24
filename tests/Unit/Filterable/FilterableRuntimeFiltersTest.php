<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;

class FilterableRuntimeFiltersTest extends TestCase
{
  // ─────────────────────────────────────────────────────────────
  //  withoutFilters()
  // ─────────────────────────────────────────────────────────────

  public function test_it_removes_a_single_filter_key_at_runtime()
  {
    $filter = filterable()->when(true, function (Filterable $f) {
      $f->setData(['title' => 'foo', 'status' => 'active', 'views' => 10]);
    });

    // Simulate $filters being set via setData — we test the $filters array
    $filter = new class extends Filterable {
      protected $filters = ['title', 'status', 'views'];
    };

    $filter->withoutFilters(['status']);

    $this->assertSame(['title', 'views'], array_values($filter->getFilterAttributes()));
  }

  public function test_it_removes_multiple_filter_keys_at_runtime()
  {
    $filter = new class extends Filterable {
      protected $filters = ['title', 'status', 'views', 'category'];
    };

    $filter->withoutFilters(['status', 'views']);

    $this->assertSame(['title', 'category'], array_values($filter->getFilterAttributes()));
  }

  public function test_it_does_nothing_when_removing_a_key_that_does_not_exist()
  {
    $filter = new class extends Filterable {
      protected $filters = ['title', 'status'];
    };

    $filter->withoutFilters(['ghost']);

    $this->assertSame(['title', 'status'], array_values($filter->getFilterAttributes()));
  }

  public function test_it_results_in_empty_filters_when_all_keys_are_removed()
  {
    $filter = new class extends Filterable {
      protected $filters = ['title', 'status'];
    };

    $filter->withoutFilters(['title', 'status']);

    $this->assertEmpty($filter->getFilterAttributes());
  }

  public function test_without_filters_returns_the_same_instance_for_chaining()
  {
    $filter = new class extends Filterable {
      protected $filters = ['title', 'status'];
    };

    $result = $filter->withoutFilters(['title']);

    $this->assertSame($filter, $result);
  }

  // ─────────────────────────────────────────────────────────────
  //  withFilters()
  // ─────────────────────────────────────────────────────────────

  public function test_it_keeps_only_the_specified_filter_keys()
  {
    $filter = new class extends Filterable {
      protected $filters = ['title', 'status', 'views', 'category'];
    };

    $filter->withFilters(['title', 'views']);

    $this->assertSame(['title', 'views'], array_values($filter->getFilterAttributes()));
  }

  public function test_it_keeps_a_single_filter_key()
  {
    $filter = new class extends Filterable {
      protected $filters = ['title', 'status', 'views'];
    };

    $filter->withFilters(['status']);

    $this->assertSame(['status'], array_values($filter->getFilterAttributes()));
  }

  public function test_it_results_in_empty_filters_when_no_keys_match()
  {
    $filter = new class extends Filterable {
      protected $filters = ['title', 'status'];
    };

    $filter->withFilters(['ghost', 'phantom']);

    $this->assertEmpty($filter->getFilterAttributes());
  }

  public function test_it_keeps_all_filters_when_all_keys_are_specified()
  {
    $filter = new class extends Filterable {
      protected $filters = ['title', 'status', 'views'];
    };

    $filter->withFilters(['title', 'status', 'views']);

    $this->assertSame(['title', 'status', 'views'], array_values($filter->getFilterAttributes()));
  }

  public function test_with_filters_returns_the_same_instance_for_chaining()
  {
    $filter = new class extends Filterable {
      protected $filters = ['title', 'status', 'views'];
    };

    $result = $filter->withFilters(['title']);

    $this->assertSame($filter, $result);
  }

  // ─────────────────────────────────────────────────────────────
  //  withFilters() + withoutFilters() combined
  // ─────────────────────────────────────────────────────────────

  public function test_it_can_chain_with_filters_and_without_filters()
  {
    $filter = new class extends Filterable {
      protected $filters = ['title', 'status', 'views', 'category'];
    };

    // Keep title + status + views, then drop status
    $filter->withFilters(['title', 'status', 'views'])
      ->withoutFilters(['status']);

    $this->assertSame(['title', 'views'], array_values($filter->getFilterAttributes()));
  }

  // ─────────────────────────────────────────────────────────────
  //  clone()
  // ─────────────────────────────────────────────────────────────

  public function test_it_returns_a_new_instance_on_clone()
  {
    $original = new class extends Filterable {
      protected $filters = ['title', 'status'];
    };

    $cloned = $original->clone();

    $this->assertNotSame($original, $cloned);
  }

  public function test_cloned_instance_is_same_class_as_original()
  {
    $original = new class extends Filterable {
      protected $filters = ['title', 'status'];
    };

    $cloned = $original->clone();

    $this->assertInstanceOf($original::class, $cloned);
  }

  public function test_mutations_on_clone_do_not_affect_original()
  {
    $original = new class extends Filterable {
      protected $filters = ['title', 'status', 'views'];
    };

    $cloned = $original->clone();
    $cloned->withoutFilters(['status', 'views']);

    // Original must be unchanged
    $this->assertSame(['title', 'status', 'views'], array_values($original->getFilterAttributes()));
    // Clone has only 'title'
    $this->assertSame(['title'], array_values($cloned->getFilterAttributes()));
  }

  public function test_mutations_on_original_do_not_affect_clone()
  {
    $original = new class extends Filterable {
      protected $filters = ['title', 'status', 'views'];
    };

    $cloned = $original->clone();
    $original->withFilters(['title']);

    // Clone must be unchanged
    $this->assertSame(['title', 'status', 'views'], array_values($cloned->getFilterAttributes()));
    // Original is narrowed
    $this->assertSame(['title'], array_values($original->getFilterAttributes()));
  }

  public function test_cloned_instance_preserves_data()
  {
    $original = new class extends Filterable {
      protected $filters = ['title', 'status'];
    };

    $original->setData(['title' => 'Hello', 'status' => 'published']);

    $cloned = $original->clone();

    $this->assertSame($original->getData(), $cloned->getData());
  }

  public function test_clone_data_changes_are_isolated_from_original()
  {
    $original = new class extends Filterable {
      protected $filters = ['title', 'status'];
    };

    $original->setData(['title' => 'Hello']);

    $cloned = $original->clone();
    $cloned->setData(['title' => 'World']);

    $this->assertSame(['title' => 'Hello'], $original->getData());
    $this->assertSame(['title' => 'World'], $cloned->getData());
  }

  public function test_clone_allowed_fields_are_isolated_from_original()
  {
    $original = new class extends Filterable {
      protected $filters = ['title', 'status'];
    };

    $original->setAllowedFields(['title']);
    $cloned = $original->clone();
    $cloned->setAllowedFields(['status']);

    $this->assertSame(['title'], $original->getAllowedFields());
    $this->assertSame(['title', 'status'], $cloned->getAllowedFields());
  }

  public function test_it_can_clone_multiple_times_independently()
  {
    $base = new class extends Filterable {
      protected $filters = ['title', 'status', 'views'];
    };

    $base->setData(['title' => 'Base']);

    $a = $base->clone()->withFilters(['title'])->setData(['title' => 'A']);
    $b = $base->clone()->withFilters(['status'])->setData(['status' => 'published']);

    $this->assertSame(['title', 'status', 'views'], array_values($base->getFilterAttributes()));
    $this->assertSame(['title'], array_values($a->getFilterAttributes()));
    $this->assertSame(['status'], array_values($b->getFilterAttributes()));
    $this->assertSame(['title' => 'A'], $a->getData());
    $this->assertSame(['status' => 'published'], $b->getData());
  }

  public function test_clone_can_be_chained_with_without_filters_and_with_filters()
  {
    $base = new class extends Filterable {
      protected $filters = ['title', 'status', 'views', 'category'];
    };

    $result = $base->clone()
      ->withFilters(['title', 'status', 'views'])
      ->withoutFilters(['views']);

    $this->assertSame(['title', 'status'], array_values($result->getFilterAttributes()));
    // Base untouched
    $this->assertSame(['title', 'status', 'views', 'category'], array_values($base->getFilterAttributes()));
  }
}
