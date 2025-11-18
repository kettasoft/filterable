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

  public function test_raw_method_returns_before_sanitize_value()
  {
    $this->assertEquals('   Filterable   ', $this->payload->raw());
  }

  public function test_is_empty_string()
  {
    $emptyString = new Payload('field', '=', '   ', '   ');
    $this->assertTrue($emptyString->isEmptyString());

    $this->assertFalse($this->payload->isEmptyString());
  }

  public function test_is_not_null_or_empty()
  {
    $this->assertTrue($this->payload->isNotNullOrEmpty());

    $nullPayload = new Payload('field', '=', null, null);
    $this->assertFalse($nullPayload->isNotNullOrEmpty());

    $emptyPayload = new Payload('field', '=', '', '');
    $this->assertFalse($emptyPayload->isNotNullOrEmpty());
  }

  public function test_is_date()
  {
    $datePayload = new Payload('field', '=', '2024-01-15', '2024-01-15');
    $this->assertTrue($datePayload->isDate());

    $nonDatePayload = new Payload('field', '=', 'not a date', 'not a date');
    $this->assertFalse($nonDatePayload->isDate());

    $numberPayload = new Payload('field', '=', 123, 123);
    $this->assertFalse($numberPayload->isDate());
  }

  public function test_is_timestamp()
  {
    $timestampPayload = new Payload('field', '=', 1705324800, 1705324800);
    $this->assertTrue($timestampPayload->isTimestamp());

    $timestampStringPayload = new Payload('field', '=', '1705324800', '1705324800');
    $this->assertTrue($timestampStringPayload->isTimestamp());

    $nonTimestampPayload = new Payload('field', '=', 'not timestamp', 'not timestamp');
    $this->assertFalse($nonTimestampPayload->isTimestamp());
  }

  public function test_as_carbon()
  {
    $datePayload = new Payload('field', '=', '2024-01-15', '2024-01-15');
    $carbon = $datePayload->asCarbon();
    $this->assertInstanceOf(\Carbon\Carbon::class, $carbon);

    $timestampPayload = new Payload('field', '=', 1705324800, 1705324800);
    $carbonFromTimestamp = $timestampPayload->asCarbon();
    $this->assertInstanceOf(\Carbon\Carbon::class, $carbonFromTimestamp);

    $nonDatePayload = new Payload('field', '=', 'invalid', 'invalid');
    $this->assertNull($nonDatePayload->asCarbon());
  }

  public function test_regex()
  {
    $emailPayload = new Payload('field', '=', 'john@example.com', 'john@example.com');
    $emailPattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

    $this->assertTrue($emailPayload->regex($emailPattern));

    $nonEmailPayload = new Payload('field', '=', 'not-an-email', 'not-an-email');
    $this->assertFalse($nonEmailPayload->regex($emailPattern));

    $numberPayload = new Payload('field', '=', 123, 123);
    $this->assertFalse($numberPayload->regex($emailPattern));
  }

  public function test_as_slug()
  {
    $payload = new Payload('field', '=', 'Hello World Test', 'Hello World Test');

    $this->assertEquals('hello-world-test', $payload->asSlug());
    $this->assertEquals('hello_world_test', $payload->asSlug('_'));
  }

  public function test_in_method()
  {
    $payload = new Payload('field', '=', 'apple', 'apple');

    $this->assertTrue($payload->in('apple', 'banana', 'orange'));
    $this->assertTrue($payload->in(['apple', 'banana', 'orange']));
    $this->assertFalse($payload->in('banana', 'orange', 'grape'));
  }

  public function test_not_in_method()
  {
    $payload = new Payload('field', '=', 'apple', 'apple');

    $this->assertFalse($payload->notIn('apple', 'banana', 'orange'));
    $this->assertTrue($payload->notIn('banana', 'orange', 'grape'));
  }

  public function test_is_method_with_multiple_checks()
  {
    $jsonPayload = new Payload('field', '=', '["a","b"]', '["a","b"]');
    $this->assertTrue($jsonPayload->is('json', 'notEmpty'));

    $emptyPayload = new Payload('field', '=', '', '');
    $this->assertFalse($emptyPayload->is('json', 'notEmpty'));
  }

  public function test_is_method_with_negation()
  {
    $stringPayload = new Payload('field', '=', 'value', 'value');

    $this->assertTrue($stringPayload->is('!empty', 'string'));
    $this->assertTrue($stringPayload->is('!null', '!numeric'));
  }

  public function test_is_method_throws_exception_for_invalid_method()
  {
    $this->expectException(\InvalidArgumentException::class);

    $payload = new Payload('field', '=', 'value', 'value');
    $payload->is('invalidCheck');
  }

  public function test_is_any_method()
  {
    $numericPayload = new Payload('field', '=', '123', '123');

    $this->assertTrue($numericPayload->isAny('numeric', 'boolean'));
    $this->assertTrue($numericPayload->isAny('array', 'numeric'));
    $this->assertFalse($numericPayload->isAny('array', 'boolean'));
  }

  public function test_is_any_method_with_negation()
  {
    $stringPayload = new Payload('field', '=', 'value', 'value');

    $this->assertTrue($stringPayload->isAny('!numeric', 'array'));
    $this->assertTrue($stringPayload->isAny('!null', '!empty'));
  }

  public function test_is_any_method_throws_exception_for_invalid_method()
  {
    $this->expectException(\InvalidArgumentException::class);

    $payload = new Payload('field', '=', 'value', 'value');
    $payload->isAny('invalidCheck');
  }

  public function test_set_value()
  {
    $this->payload->setValue('NewValue');
    $this->assertEquals('NewValue', $this->payload->value);
  }

  public function test_set_field()
  {
    $this->payload->setField('newField');
    $this->assertEquals('newField', $this->payload->field);
  }

  public function test_set_operator()
  {
    $this->payload->setOperator('!=');
    $this->assertEquals('!=', $this->payload->operator);
  }

  public function test_get_field()
  {
    $this->assertEquals('name', $this->payload->getField());
  }

  public function test_get_operator()
  {
    $this->assertEquals('=', $this->payload->getOperator());
  }

  public function test_explode_method()
  {
    $payload = new Payload('field', '=', 'apple,banana,orange', 'apple,banana,orange');

    $this->assertEquals(['apple', 'banana', 'orange'], $payload->explode(','));
    $this->assertEquals(['apple,banana,orange'], $payload->explode('|'));
  }

  public function test_explode_with_array_value()
  {
    $payload = new Payload('field', 'in', ['apple', 'banana', 'orange'], ['apple', 'banana', 'orange']);

    $this->assertEquals(['apple', 'banana', 'orange'], $payload->explode(','));
  }

  public function test_split_method()
  {
    $payload = new Payload('field', '=', 'one|two|three', 'one|two|three');

    $this->assertEquals(['one', 'two', 'three'], $payload->split('|'));
  }

  public function test_as_like_throws_exception_for_invalid_side()
  {
    $this->expectException(\InvalidArgumentException::class);

    $payload = new Payload('field', 'like', 'search', 'search');
    $payload->asLike('invalid');
  }

  public function test_chaining_setter_methods()
  {
    $payload = new Payload('oldField', '=', 'oldValue', 'oldValue');

    $result = $payload
      ->setField('newField')
      ->setOperator('!=')
      ->setValue('newValue');

    $this->assertInstanceOf(Payload::class, $result);
    $this->assertEquals('newField', $payload->field);
    $this->assertEquals('!=', $payload->operator);
    $this->assertEquals('newValue', $payload->value);
  }

  public function test_implements_arrayable_interface()
  {
    $this->assertInstanceOf(\Illuminate\Contracts\Support\Arrayable::class, $this->payload);
  }

  public function test_implements_jsonable_interface()
  {
    $this->assertInstanceOf(\Illuminate\Contracts\Support\Jsonable::class, $this->payload);
  }

  public function test_implements_stringable_interface()
  {
    $this->assertInstanceOf(\Stringable::class, $this->payload);
  }

  public function test_macros()
  {
    Payload::macro('isEmail', function () {
      return $this->regex('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/');
    });

    $emailPayload = new Payload('field', '=', 'test@example.com', 'test@example.com');
    $nonEmailPayload = new Payload('field', '=', 'not-email', 'not-email');

    $this->assertTrue($emailPayload->isEmail());
    $this->assertFalse($nonEmailPayload->isEmail());
  }

  public function test_handles_unicode_strings()
  {
    $payload = new Payload('field', '=', 'Hello 世界', 'Hello 世界');

    $this->assertEquals(8, $payload->length());
    $this->assertTrue($payload->isString());
    // Slug removes non-ASCII characters if transliteration is not available
    $slug = $payload->asSlug();
    $this->assertIsString($slug);
    $this->assertStringContainsString('hello', $slug);
  }

  public function test_handles_empty_array()
  {
    $payload = new Payload('field', 'in', [], []);

    $this->assertTrue($payload->isEmpty());
    $this->assertTrue($payload->isArray());
    $this->assertEquals(0, $payload->length());
  }

  public function test_handles_nested_json()
  {
    $nestedJson = '{"user":{"name":"John","address":{"city":"NYC"}}}';
    $payload = new Payload('field', '=', $nestedJson, $nestedJson);

    $this->assertTrue($payload->isJson());
    $this->assertEquals([
      'user' => [
        'name' => 'John',
        'address' => ['city' => 'NYC']
      ]
    ], $payload->asArray());
  }

  public function test_handles_mixed_type_values()
  {
    $values = [
      'string' => 'test',
      'integer' => 123,
      'float' => 45.67,
      'boolean' => true,
      'array' => ['a', 'b'],
      'null' => null,
    ];

    foreach ($values as $type => $value) {
      $payload = new Payload('field', '=', $value, $value);
      $this->assertInstanceOf(Payload::class, $payload);
      $this->assertEquals($value, $payload->value);
    }
  }

  public function test_as_boolean_returns_null_for_non_boolean()
  {
    $payload = new Payload('field', '=', 'random', 'random');
    $this->assertNull($payload->asBoolean());
  }

  public function test_as_int_returns_null_for_non_numeric()
  {
    $payload = new Payload('field', '=', 'abc', 'abc');
    $this->assertNull($payload->asInt());
  }

  public function test_as_array_returns_null_for_non_array_non_json()
  {
    $payload = new Payload('field', '=', 'string', 'string');
    $this->assertNull($payload->asArray());
  }

  public function test_json_array_conversion()
  {
    $jsonArray = '["apple","banana","orange"]';
    $payload = new Payload('field', '=', $jsonArray, $jsonArray);

    $this->assertTrue($payload->isJson());
    $this->assertEquals(['apple', 'banana', 'orange'], $payload->asArray());
  }

  public function test_boolean_variations()
  {
    $variations = [
      ['value' => true, 'expected' => true],
      ['value' => false, 'expected' => false],
      ['value' => 1, 'expected' => true],
      ['value' => 0, 'expected' => false],
      ['value' => '1', 'expected' => true],
      ['value' => '0', 'expected' => false],
      ['value' => 'true', 'expected' => true],
      ['value' => 'false', 'expected' => false],
      ['value' => 'yes', 'expected' => true],
      ['value' => 'no', 'expected' => false],
    ];

    foreach ($variations as $variation) {
      $payload = new Payload('field', '=', $variation['value'], $variation['value']);
      $this->assertTrue($payload->isBoolean());
      $this->assertEquals($variation['expected'], $payload->asBoolean());
    }
  }

  public function test_numeric_variations()
  {
    $variations = [
      123,
      '123',
      45.67,
      '45.67',
      -100,
      '-100',
    ];

    foreach ($variations as $value) {
      $payload = new Payload('field', '=', $value, $value);
      $this->assertTrue($payload->isNumeric());
    }
  }

  public function test_date_variations()
  {
    $variations = [
      '2024-01-15',
      '2024-01-15 10:30:00',
      'January 15, 2024',
      'tomorrow',
      'yesterday',
      '+1 day',
    ];

    foreach ($variations as $value) {
      $payload = new Payload('field', '=', $value, $value);
      $this->assertTrue($payload->isDate(), "Failed asserting that '$value' is a valid date");
    }
  }

  public function test_edge_case_empty_json_object()
  {
    $payload = new Payload('field', '=', '{}', '{}');

    $this->assertTrue($payload->isJson());
    $this->assertEquals([], $payload->asArray());
  }

  public function test_edge_case_empty_json_array()
  {
    $payload = new Payload('field', '=', '[]', '[]');

    $this->assertTrue($payload->isJson());
    $this->assertEquals([], $payload->asArray());
  }
}
