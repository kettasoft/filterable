<?php

namespace Kettasoft\Filterable\Tests\Unit\Engines\Utilities;

use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Engines\Foundation\Mappers\ClauseKeyMapper;

class ClauseKeyMapperTest extends TestCase
{
  /**
   * @test
   */
  public function it_uses_default_keys_when_none_are_provided()
  {
    $mapper = new ClauseKeyMapper();

    $this->assertEquals('field', $mapper->field());
    $this->assertEquals('operator', $mapper->operator());
    $this->assertEquals('value', $mapper->value());
  }

  /**
   * @test
   */
  public function it_allow_custom_keys()
  {
    $mapper = new ClauseKeyMapper([
      'field' => 'f',
      'operator' => 'o',
      'value' => 'v',
    ]);

    $this->assertEquals('f', $mapper->field());
    $this->assertEquals('o', $mapper->operator());
    $this->assertEquals('v', $mapper->value());
  }

  /**
   * @test
   */
  public function it_thorws_exception_when_keys_are_not_unique()
  {
    $this->expectException(\InvalidArgumentException::class);

    $this->expectExceptionMessage("Custom clause keys must be unique");

    new ClauseKeyMapper([
      'field' => 'f',
      'operator' => 'o',
      'value' => 'f' // Conflict with 'field'
    ]);
  }
  /**
   * @test
   */
  public function it_thorws_exception_when_all_keys_are_the_same()
  {
    $this->expectException(\InvalidArgumentException::class);

    new ClauseKeyMapper([
      'field' => 'same',
      'operator' => 'same',
      'value' => 'same'
    ]);
  }

  /**
   * @test
   */
  public function it_can_accepts_custom_keys_from_config()
  {
    config()->set('filterable.clause_keys', [
      'field' => 'f',
      'operator' => 'o',
      'value' => 'v',
    ]);

    $mapper = new ClauseKeyMapper();

    $this->assertEquals('f', $mapper->field());
    $this->assertEquals('o', $mapper->operator());
    $this->assertEquals('v', $mapper->value());
  }

  /**
   * @test
   */
  public function it_can_accepts_partial_custom_keys_and_uses_defaults_for_rest()
  {
    $mapper = new ClauseKeyMapper([
      'field' => 'f'
    ]);

    $this->assertEquals('f', $mapper->field());
    $this->assertEquals('operator', $mapper->operator());
    $this->assertEquals('value', $mapper->value());
  }
}
