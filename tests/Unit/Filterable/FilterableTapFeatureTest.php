<?php
namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;

class FilterableTapFeatureTest extends TestCase
{
    public function test_it_can_use_tap_feature(): void
    {
        $filterable = Filterable::tap(function (Filterable $filterable) {
            $filterable->setAllowedFields(['name']);
        });
        
        $this->assertInstanceOf(Filterable::class, $filterable);
        $this->assertEquals(['name'], $filterable->getAllowedFields());
    }
}