<?php

namespace Kettasoft\Filterable\Support;

use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;

/**
 * @template TKey of array-key
 * @template TValue
 */
class Payload implements \Stringable, Arrayable, Jsonable
{
  /**
   * Request field.
   * @var string
   */
  public string $field;

  /**
   * Requested operator.
   * @var string
   */
  public string $operator;

  /**
   * Request value.
   * @var mixed
   */
  public mixed $value;

  /**
   * Value before sanitizing.
   * @var mixed
   */
  public mixed $beforeSanitize;

  /**
   * Create new Payload instance.
   * @param string $field
   * @param string $operator
   * @param mixed $value
   * @param mixed $beforeSanitize
   */
  public function __construct(string $field, string $operator, mixed $value, mixed $beforeSanitize)
  {
    $this->field = $field;
    $this->operator = $operator;
    $this->value = $value;
    $this->beforeSanitize = $beforeSanitize;
  }

  /**
   * Shortcut to create Payload instance.
   * @param mixed $field
   * @param mixed $operator
   * @param mixed $value
   * @param mixed $beforeSanitize
   * @return Payload
   */
  public static function create($field, $operator, $value, $beforeSanitize): static
  {
    return new static($field, $operator, $value, $beforeSanitize);
  }

  /**
   * Get the original unmodified value.
   * 
   * @return mixed
   */
  public function raw(): mixed
  {
    return $this->beforeSanitize;
  }

  /**
   * Get the length of the payload value.
   * 
   * @return int
   */
  public function length(): int
  {
    return is_string($this->value) ? mb_strlen($this->value) : count((array) $this->value);
  }

  /**
   * Check if the payload is empty.
   *
   * @return bool
   */
  public function isEmpty(): bool
  {
    return empty($this->value);
  }

  /**
   * Check if the payload is an empty string.
   *
   * @return bool
   */
  public function isEmptyString(): bool
  {
    return is_string($this->value) && trim($this->value) === '';
  }

  /**
   * Check if the payload is not null or empty.
   *
   * @return bool
   */
  public function isNotNullOrEmpty(): bool
  {
    return !$this->isNull() && !$this->isEmpty() && !$this->isEmptyString();
  }

  /**
   * Check if the payload is not empty.
   *
   * @return bool
   */
  public function isNotEmpty(): bool
  {
    return !$this->isEmpty();
  }

  /**
   * Check if the payload value is null.
   *
   * @return bool
   */
  public function isNull(): bool
  {
    return is_null($this->value);
  }

  /**
   * Check if the payload value is a boolean.
   *
   * @return bool
   */
  public function isBoolean(): bool
  {
    if (is_bool($this->value)) {
      return true;
    }

    return filter_var($this->value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null;
  }

  /**
   * Check if the payload value is a valid JSON string.
   *
   * @param bool $strict When true, only JSON objects/arrays are considered valid.
   *                     When false, any valid JSON (string, number, boolean, null, object, array) is accepted.
   * @return bool
   */
  public function isJson(bool $strict = true): bool
  {
    if (!is_string($this->value)) {
      return false;
    }

    $decoded = json_decode($this->value, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      return false;
    }

    if ($strict) {
      return is_array($decoded);
    }

    return true;
  }

  /**
   * Check if the payload value is numeric.
   * 
   * @return bool
   */
  public function isNumeric(): bool
  {
    return is_numeric($this->value);
  }

  /**
   * Check if the payload value is a string.
   *
   * @return bool
   */
  public function isString(): bool
  {
    return is_string($this->value);
  }

  /**
   * Check if the payload value is an array.
   *
   * @return bool
   */
  public function isArray(): bool
  {
    return is_array($this->value);
  }

  /**
   * Check if the payload value is true.
   *
   * @return bool
   */
  public function isTrue(): bool
  {
    return $this->isBoolean() && filter_var($this->value, FILTER_VALIDATE_BOOLEAN) === true;
  }

  /**
   * Check if the payload value is false.
   *
   * @return bool
   */
  public function isFalse(): bool
  {
    return $this->isBoolean() && filter_var($this->value, FILTER_VALIDATE_BOOLEAN) === false;
  }

  /**
   * Check if the payload value matches the given regex pattern.
   * 
   * @param string $pattern
   * @return bool
   */
  public function regex(string $pattern): bool
  {
    if (! $this->isString()) {
      return false;
    }

    return preg_match($pattern, $this->value) === 1;
  }

  /**
   * Get the payload value as a boolean.
   *
   * Returns `true` or `false` if the value can be interpreted as a boolean
   * (e.g. actual bool, "true", "false", 1, 0, "1", "0"), otherwise returns null.
   *
   * @return bool|null
   */
  public function asBoolean(): ?bool
  {
    return $this->isBoolean() ? filter_var($this->value, FILTER_VALIDATE_BOOLEAN) : null;
  }

  /**
   * Convert the payload value to a slug.
   * 
   * @param string $operator
   * @return string
   */
  public function asSlug(string $operator = '-'): string
  {
    $value = (string) $this->value;

    return Str::slug($value, $operator);
  }

  /**
   * Check if the payload value is in the given haystack.
   *
   * @param mixed ...$haystack
   * @return bool
   */
  public function in(...$haystack): bool
  {
    if (count($haystack) === 1 && is_array($haystack[0])) {
      $haystack = $haystack[0];
    }

    return in_array($this->value, (array) $haystack, true);
  }

  /**
   * Check if the payload value is not in the given haystack.
   *
   * @param mixed ...$haystack
   * @return bool
   */
  public function notIn(...$haystack): bool
  {
    return !$this->in(...$haystack);
  }

  /**
   * Perform multiple is* checks on the payload.
   *
   * Example: $payload->is('isJson', 'isNotEmpty')
   *
   * @param mixed ...$checks
   * @return bool
   *
   * @throws \InvalidArgumentException if any of the check methods do not exist.
   */
  public function is(...$checks): bool
  {
    foreach ($checks as $check) {
      $negate = false;

      // If there is a ! at the beginning, negate the result
      if (str_starts_with($check, '!')) {
        $negate = true;
        $check = substr($check, 1); // Remove the '!' for method name
      }

      $method = 'is' . ucfirst($check);

      if (!method_exists($this, $method)) {
        throw new \InvalidArgumentException("Method $method does not exist");
      }

      $result = $this->$method();

      if ($negate) {
        $result = !$result;
      }

      if (!$result) {
        return false; // Any check failed, return false immediately
      }
    }

    return true;
  }

  /**
   * Perform multiple is* checks on the payload, returning true if any pass.
   *
   * Example: $payload->isAny('isJson', 'isNotEmpty')
   *
   * @param mixed ...$checks
   * @return bool
   *
   * @throws \InvalidArgumentException if any of the check methods do not exist.
   */
  public function isAny(...$checks): bool
  {
    foreach ($checks as $check) {
      $negate = false;

      // If there is a ! at the beginning, negate the result
      if (str_starts_with($check, '!')) {
        $negate = true;
        $check = substr($check, 1); // Remove the '!' for method name
      }

      $method = 'is' . ucfirst($check);

      if (!method_exists($this, $method)) {
        throw new \InvalidArgumentException("Method $method does not exist");
      }

      $result = $this->$method();

      if ($negate) {
        $result = !$result;
      }

      if ($result) {
        return true; // Any check passed, return true immediately
      }
    }

    return false;
  }

  /**
   * Return a new Payload instance with the given value.
   *
   * @param mixed $value
   * @return Payload
   */
  public function setValue(mixed $value): Payload
  {
    $this->value = $value;
    return $this;
  }

  /**
   * Set the payload field.
   *
   * @param string $field
   * @return Payload
   */
  public function setField(string $field): Payload
  {
    $this->field = $field;
    return $this;
  }

  /**
   * Set the payload operator.
   *
   * @param string $operator
   * @return Payload
   */
  public function setOperator(string $operator): Payload
  {
    $this->operator = $operator;
    return $this;
  }

  /**
   * Get the payload field.
   *
   * @return string
   */
  public function getField(): string
  {
    return $this->field;
  }

  /**
   * Get the payload operator.
   * 
   * @return string
   */
  public function getOperator(): string
  {
    return $this->operator;
  }

  /**
   * Get the payload value as an array.
   *
   * If the value is a valid JSON string representing an array/object,
   * it will be decoded into an array. If the value is already an array,
   * it will be returned directly. Otherwise returns null.
   *
   * @return array<TKey, TValue>|null
   */
  public function asArray(): ?array
  {
    return $this->isJson() ? json_decode($this->value, true) : (is_array($this->value) ? $this->value : null);
  }

  /**
   * Get the payload value as an integer.
   *
   * If the value is numeric, it will be cast to int. Otherwise returns null.
   *
   * @return int|null
   */
  public function asInt(): ?int
  {
    return $this->isNumeric() ? (int) $this->value : null;
  }

  /**
   * Wrap the value with a given prefix and suffix.
   *
   * @param string $prefix
   * @param string $suffix
   * @return string
   */
  protected function wrap(string $prefix, string $suffix): string
  {
    return sprintf('%s%s%s', $prefix, $this->value, $suffix);
  }

  /**
   * Get the value wrapped for a LIKE query.
   *
   * Example: "%value%", "value%", "%value", etc.
   *
   * @param string $side
   * @return string
   */
  public function asLike(string $side = 'both'): string
  {
    return match ($side) {
      'both' => $this->wrap('%', '%'),
      'start' => $this->wrap('%', ''),
      'end' => $this->wrap('', '%'),
      default => throw new \InvalidArgumentException(sprintf("The side value is not valid. valid sides: %s, %s, %s", 'both', 'start', 'end'))
    };
  }

  /**
   * Get the instance as an array.
   *
   * @return array<TKey, TValue>
   */
  public function toArray()
  {
    return [
      'field' => $this->field,
      'operator' => $this->operator,
      'value' => $this->value,
      'beforeSanitize' => $this->beforeSanitize,
    ];
  }

  /**
   * Convert the object to its JSON representation.
   *
   * @param  int  $options
   * @return string
   */
  public function toJson($options = 0)
  {
    return json_encode($this->toArray(), $options);
  }

  /**
   * Return request value on read class as a string.
   */
  public function __toString()
  {
    return $this->value;
  }
}
