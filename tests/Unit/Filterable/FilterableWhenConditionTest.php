<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;

class FilterableWhenConditionTest extends TestCase
{
  public function test_it_can_use_singel_when()
  {
    $filter = Filterable::create()->when(true, function (Filterable $filter) {
      $filter->setAllowedFields(['test']);
    });

    $this->assertNotEmpty($filter->getAllowedFields());
  }

  public function test_it_cant_invoke_when_callback_with_false_condition()
  {
    $filter = Filterable::create()->when(false, function (Filterable $filter) {
      $filter->setAllowedFields(['test']);
    });

    $this->assertEmpty($filter->getAllowedFields());
  }

  public function test_it_can_use_nested_when()
  {
    $filter = Filterable::create()->when(true, function (Filterable $filter) {
      $filter->setAllowedFields(['test1']);

      $filter->when(true, function (Filterable $filter) {
        $filter->setAllowedFields(['test2', 'test3']);

        $filter->when(true, fn($filter) => $filter->setAllowedFields(['test4']));

        // Not working
        $filter->when(false, function (Filterable $filter) {
          $filter->setAllowedFields(['test2', 'test3']);
        });
      });
    });

    $this->assertCount(4, $filter->getAllowedFields());
  }

  public function test_it_can_use_unless()
  {
    $filter = Filterable::create()->unless(false, function (Filterable $filter) {
      $filter->setAllowedFields(['test1']);
    })->unless(true, function (Filterable $filter) {
      $filter->setAllowedFields(['test2']);
    });

    $this->assertCount(1, $filter->getAllowedFields());
  }

  public function test_it_can_use_mixed_when_unless()
  {
    $filter = Filterable::create()
      ->when(true, function (Filterable $filter) {
        $filter->setAllowedFields(['test1']);
      })
      ->unless(false, function (Filterable $filter) {
        $filter->setAllowedFields(['test2']);
      })
      ->when(false, function (Filterable $filter) {
        $filter->setAllowedFields(['test3']);
      })
      ->unless(true, function (Filterable $filter) {
        $filter->setAllowedFields(['test4']);
      });

    $this->assertCount(2, $filter->getAllowedFields());
  }

  public function test_it_can_use_nested_mixed_when_unless()
  {
    $filter = Filterable::create()
      ->when(true, function (Filterable $filter) {
        $filter->setAllowedFields(['test1']);

        $filter->unless(false, function (Filterable $filter) {
          $filter->setAllowedFields(['test2']);

          $filter->when(true, fn($filter) => $filter->setAllowedFields(['test3']));

          $filter->unless(true, function (Filterable $filter) {
            $filter->setAllowedFields(['test4']);
          });
        });
      });

    $this->assertCount(3, $filter->getAllowedFields());
  }
}
