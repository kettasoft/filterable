<?php

namespace Kettasoft\Filterable\Tests\Unit\Sanitization;

use Countable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Sanitization\Sanitizer;

class SanitizerCountTest extends TestCase
{
  /**
   * It can get number of sanitizers via count keywork.
   */
  public function test_it_can_get_number_of_sanitizers_via_count_keywork()
  {
    $sanitizer = new Sanitizer([function () {}]);

    $this->assertInstanceOf(Countable::class, $sanitizer);
    $this->assertEquals(1, count($sanitizer));
  }
}
