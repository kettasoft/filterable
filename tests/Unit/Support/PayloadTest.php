<?php

namespace Kettasoft\Filterable\Tests\Unit\Support;

use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Support\Payload;

class PayloadTest extends TestCase
{
  protected Payload $payload;

  public function setUp(): void
  {
    parent::setUp();

    $this->payload = new Payload(
      field: 'name',
      operator: '=',
      value: 'Filterable',
      beforeSanitize: '   Filterable   '
    );
  }

  public function test_it_can_be_instantiated()
  {
    $this->assertInstanceOf(Payload::class, $this->payload);
    $this->assertEquals('name', $this->payload->field);
    $this->assertEquals('=', $this->payload->operator);
    $this->assertEquals('Filterable', $this->payload->value);
    $this->assertEquals('   Filterable   ', $this->payload->beforeSanitize);
  }

  public function test_static_create_method()
  {
    $payload = Payload::create('age', '>', 25, '25');
    $this->assertInstanceOf(Payload::class, $payload);
    $this->assertEquals(25, $payload->value);
  }

  public function test_length_method()
  {
    $this->assertEquals(10, $this->payload->length());

    $arrayPayload = new Payload('tags', 'IN', ['php', 'laravel'], ['php', 'laravel']);
    $this->assertEquals(2, $arrayPayload->length());
  }

  public function test_empty_and_not_empty()
  {
    $emptyPayload = new Payload('name', '=', '', '');
    $this->assertTrue($emptyPayload->isEmpty());
    $this->assertFalse($emptyPayload->isNotEmpty());

    $this->assertTrue($this->payload->isNotEmpty());
  }

  public function test_null_check()
  {
    $nullPayload = new Payload('name', '=', null, null);
    $this->assertTrue($nullPayload->isNull());
  }

  public function test_boolean_checks()
  {
    $truePayload = new Payload('active', '=', true, true);
    $falsePayload = new Payload('active', '=', 'false', 'false');

    $this->assertTrue($truePayload->isBoolean());
    $this->assertTrue($falsePayload->isBoolean());

    $this->assertTrue($truePayload->isTrue());
    $this->assertTrue($falsePayload->isFalse());

    $this->assertTrue($truePayload->asBoolean());
    $this->assertFalse($falsePayload->asBoolean());
  }

  public function test_json_checks()
  {
    $jsonPayload = new Payload('data', '=', '{"name":"John"}', '{"name":"John"}');
    $this->assertTrue($jsonPayload->isJson());
    $this->assertIsArray($jsonPayload->asArray());
    $this->assertEquals(['name' => 'John'], $jsonPayload->asArray());

    $strictInvalid = new Payload('data', '=', '"string"', '"string"');
    $this->assertFalse($strictInvalid->isJson(true));
    $this->assertTrue($strictInvalid->isJson(false));
  }

  public function test_numeric_checks()
  {
    $numericPayload = new Payload('age', '=', '42', '42');
    $this->assertTrue($numericPayload->isNumeric());
    $this->assertEquals(42, $numericPayload->asInt());
  }

  public function test_string_and_array_checks()
  {
    $this->assertTrue($this->payload->isString());

    $arrayPayload = new Payload('ids', 'IN', [1, 2, 3], [1, 2, 3]);
    $this->assertTrue($arrayPayload->isArray());
  }

  public function test_wrap_and_like_helpers()
  {
    $this->assertEquals('%Filterable%', $this->payload->asLike());
    $this->assertEquals('%Filterable', $this->payload->asLike('start'));
    $this->assertEquals('Filterable%', $this->payload->asLike('end'));
  }

  public function test_to_array_and_json()
  {
    $array = $this->payload->toArray();
    $this->assertArrayHasKey('field', $array);
    $this->assertArrayHasKey('operator', $array);

    $json = $this->payload->toJson();
    $this->assertJson($json);
  }

  public function test_to_string_returns_value()
  {
    $this->assertEquals('Filterable', (string) $this->payload);
  }
}
