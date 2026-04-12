<?php

namespace Kettasoft\Filterable\Tests\Unit\Sanitization;

use Kettasoft\Filterable\Sanitization\Sanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\TrimSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\LowercaseSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\UppercaseSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\StripTagsSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\EscapeHtmlSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\IntegerSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\FloatSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\BooleanSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\SlugSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\NullIfEmptySanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\ClampSanitizer;
use Kettasoft\Filterable\Sanitization\Defaults\StripSpecialCharsSanitizer;
use Kettasoft\Filterable\Tests\TestCase;

class SanitizerDefaultsTest extends TestCase
{
  // ─────────────────────────────────────────────
  // TrimSanitizer
  // ─────────────────────────────────────────────

  public function test_trim_sanitizer_removes_whitespace_from_strings()
  {
    $s = new TrimSanitizer;
    $this->assertSame('hello', $s->sanitize('  hello  '));
  }

  public function test_trim_sanitizer_trims_each_element_in_array()
  {
    $s = new TrimSanitizer;
    $this->assertSame(['hello', 'world'], $s->sanitize(['  hello', 'world  ']));
  }

  public function test_trim_sanitizer_passes_non_string_unchanged()
  {
    $s = new TrimSanitizer;
    $this->assertSame(42, $s->sanitize(42));
    $this->assertNull($s->sanitize(null));
  }

  public function test_trim_alias_resolves_via_handler_factory()
  {
    $this->assertSame('hello', Sanitizer::apply('  hello  ', 'trim'));
  }

  // ─────────────────────────────────────────────
  // LowercaseSanitizer
  // ─────────────────────────────────────────────

  public function test_lowercase_sanitizer_converts_string_to_lowercase()
  {
    $s = new LowercaseSanitizer;
    $this->assertSame('hello world', $s->sanitize('Hello World'));
  }

  public function test_lowercase_sanitizer_handles_multibyte()
  {
    $s = new LowercaseSanitizer;
    $this->assertSame('héllo', $s->sanitize('HÉLLO'));
  }

  public function test_lowercase_sanitizer_processes_array()
  {
    $s = new LowercaseSanitizer;
    $this->assertSame(['foo', 'bar'], $s->sanitize(['FOO', 'BAR']));
  }

  public function test_lowercase_alias_resolves_via_handler_factory()
  {
    $this->assertSame('hello', Sanitizer::apply('HELLO', 'lowercase'));
  }

  // ─────────────────────────────────────────────
  // UppercaseSanitizer
  // ─────────────────────────────────────────────

  public function test_uppercase_sanitizer_converts_string_to_uppercase()
  {
    $s = new UppercaseSanitizer;
    $this->assertSame('HELLO WORLD', $s->sanitize('hello world'));
  }

  public function test_uppercase_sanitizer_processes_array()
  {
    $s = new UppercaseSanitizer;
    $this->assertSame(['FOO', 'BAR'], $s->sanitize(['foo', 'bar']));
  }

  public function test_uppercase_alias_resolves_via_handler_factory()
  {
    $this->assertSame('HELLO', Sanitizer::apply('hello', 'uppercase'));
  }

  // ─────────────────────────────────────────────
  // StripTagsSanitizer
  // ─────────────────────────────────────────────

  public function test_strip_tags_sanitizer_removes_html_tags()
  {
    $s = new StripTagsSanitizer;
    $this->assertSame('Hello ', $s->sanitize('<b>Hello</b> '));
  }

  public function test_strip_tags_sanitizer_removes_script_tags()
  {
    $s = new StripTagsSanitizer;
    // strip_tags removes the <script> tag but preserves inner text content
    $this->assertSame('alert(1)safe', $s->sanitize('<script>alert(1)</script>safe'));
  }

  public function test_strip_tags_sanitizer_allows_specified_tags()
  {
    $s = new StripTagsSanitizer('<b>');
    // <script> tag is stripped but its inner text 'x' is preserved
    $this->assertSame('<b>Hello</b>x', $s->sanitize('<b>Hello</b><script>x</script>'));
  }

  public function test_strip_tags_sanitizer_processes_array()
  {
    $s = new StripTagsSanitizer;
    $this->assertSame(['Hello', 'World'], $s->sanitize(['<b>Hello</b>', '<i>World</i>']));
  }

  public function test_strip_tags_alias_resolves_via_handler_factory()
  {
    $this->assertSame('Hello', Sanitizer::apply('<b>Hello</b>', 'strip_tags'));
  }

  // ─────────────────────────────────────────────
  // EscapeHtmlSanitizer
  // ─────────────────────────────────────────────

  public function test_escape_html_sanitizer_escapes_special_chars()
  {
    $s = new EscapeHtmlSanitizer;
    $this->assertSame('&lt;b&gt;Hi&lt;/b&gt;', $s->sanitize('<b>Hi</b>'));
  }

  public function test_escape_html_sanitizer_escapes_quotes()
  {
    $s = new EscapeHtmlSanitizer;
    $this->assertSame('say &quot;hi&quot;', $s->sanitize('say "hi"'));
  }

  public function test_escape_html_sanitizer_processes_array()
  {
    $s = new EscapeHtmlSanitizer;
    $result = $s->sanitize(['<b>a</b>', '<i>b</i>']);
    $this->assertSame(['&lt;b&gt;a&lt;/b&gt;', '&lt;i&gt;b&lt;/i&gt;'], $result);
  }

  public function test_escape_html_alias_resolves_via_handler_factory()
  {
    $this->assertSame('&lt;b&gt;', Sanitizer::apply('<b>', 'escape_html'));
  }

  // ─────────────────────────────────────────────
  // IntegerSanitizer
  // ─────────────────────────────────────────────

  public function test_integer_sanitizer_casts_numeric_string_to_int()
  {
    $s = new IntegerSanitizer;
    $this->assertSame(42, $s->sanitize('42'));
  }

  public function test_integer_sanitizer_strips_non_numeric_suffix()
  {
    $s = new IntegerSanitizer;
    $this->assertSame(42, $s->sanitize('42abc'));
  }

  public function test_integer_sanitizer_returns_null_on_fail_when_configured()
  {
    $s = new IntegerSanitizer(nullOnFail: true);
    $this->assertNull($s->sanitize('abc'));
  }

  public function test_integer_sanitizer_processes_array()
  {
    $s = new IntegerSanitizer;
    $this->assertSame([1, 2, 3], $s->sanitize(['1', '2', '3']));
  }

  public function test_integer_alias_resolves_via_handler_factory()
  {
    $this->assertSame(7, Sanitizer::apply('7', 'integer'));
  }

  // ─────────────────────────────────────────────
  // FloatSanitizer
  // ─────────────────────────────────────────────

  public function test_float_sanitizer_casts_value_to_float()
  {
    $s = new FloatSanitizer;
    $this->assertSame(3.14, $s->sanitize('3.14'));
  }

  public function test_float_sanitizer_rounds_to_given_decimals()
  {
    $s = new FloatSanitizer(decimals: 2);
    $this->assertSame(3.14, $s->sanitize('3.1415'));
  }

  public function test_float_sanitizer_processes_array()
  {
    $s = new FloatSanitizer(decimals: 1);
    $this->assertSame([1.1, 2.3], $s->sanitize(['1.14', '2.25']));
  }

  public function test_float_alias_resolves_via_handler_factory()
  {
    $this->assertSame(1.5, Sanitizer::apply('1.5', 'float'));
  }

  // ─────────────────────────────────────────────
  // BooleanSanitizer
  // ─────────────────────────────────────────────

  public function test_boolean_sanitizer_converts_truthy_strings()
  {
    $s = new BooleanSanitizer;
    foreach (['true', '1', 'yes', 'on', 'TRUE', 'YES'] as $v) {
      $this->assertTrue($s->sanitize($v), "Expected true for: $v");
    }
  }

  public function test_boolean_sanitizer_converts_falsy_strings()
  {
    $s = new BooleanSanitizer;
    foreach (['false', '0', 'no', 'off', '', 'FALSE'] as $v) {
      $this->assertFalse($s->sanitize($v), "Expected false for: $v");
    }
  }

  public function test_boolean_sanitizer_passes_native_booleans()
  {
    $s = new BooleanSanitizer;
    $this->assertTrue($s->sanitize(true));
    $this->assertFalse($s->sanitize(false));
  }

  public function test_boolean_sanitizer_handles_integer()
  {
    $s = new BooleanSanitizer;
    $this->assertTrue($s->sanitize(1));
    $this->assertFalse($s->sanitize(0));
  }

  public function test_boolean_alias_resolves_via_handler_factory()
  {
    $this->assertTrue(Sanitizer::apply('yes', 'boolean'));
    $this->assertFalse(Sanitizer::apply('no', 'boolean'));
  }

  // ─────────────────────────────────────────────
  // SlugSanitizer
  // ─────────────────────────────────────────────

  public function test_slug_sanitizer_converts_to_slug()
  {
    $s = new SlugSanitizer;
    $this->assertSame('hello-world', $s->sanitize('Hello World!'));
  }

  public function test_slug_sanitizer_uses_custom_separator()
  {
    $s = new SlugSanitizer(separator: '_');
    $this->assertSame('hello_world', $s->sanitize('Hello World'));
  }

  public function test_slug_sanitizer_processes_array()
  {
    $s = new SlugSanitizer;
    $this->assertSame(['foo-bar', 'baz'], $s->sanitize(['Foo Bar', 'Baz']));
  }

  public function test_slug_alias_resolves_via_handler_factory()
  {
    $this->assertSame('hello-world', Sanitizer::apply('Hello World', 'slug'));
  }

  // ─────────────────────────────────────────────
  // NullIfEmptySanitizer
  // ─────────────────────────────────────────────

  public function test_null_if_empty_sanitizer_returns_null_for_empty_string()
  {
    $s = new NullIfEmptySanitizer;
    $this->assertNull($s->sanitize(''));
  }

  public function test_null_if_empty_sanitizer_returns_null_for_default_empty_values()
  {
    $s = new NullIfEmptySanitizer;
    foreach (['0', 'null', 'undefined', 'none'] as $v) {
      $this->assertNull($s->sanitize($v), "Expected null for: $v");
    }
  }

  public function test_null_if_empty_sanitizer_preserves_non_empty_string()
  {
    $s = new NullIfEmptySanitizer;
    $this->assertSame('hello', $s->sanitize('hello'));
  }

  public function test_null_if_empty_sanitizer_returns_null_for_null_input()
  {
    $s = new NullIfEmptySanitizer;
    $this->assertNull($s->sanitize(null));
  }

  public function test_null_if_empty_sanitizer_processes_array()
  {
    $s = new NullIfEmptySanitizer;
    $this->assertSame([null, 'hello', null], $s->sanitize(['', 'hello', 'null']));
  }

  public function test_null_if_empty_alias_resolves_via_handler_factory()
  {
    $this->assertNull(Sanitizer::apply('', 'null_if_empty'));
  }

  // ─────────────────────────────────────────────
  // ClampSanitizer
  // ─────────────────────────────────────────────

  public function test_clamp_sanitizer_enforces_maximum()
  {
    $s = new ClampSanitizer(max: 100);
    $this->assertSame(100, $s->sanitize(150));
  }

  public function test_clamp_sanitizer_enforces_minimum()
  {
    $s = new ClampSanitizer(min: 1);
    $this->assertSame(1, $s->sanitize(0));
  }

  public function test_clamp_sanitizer_does_not_change_value_within_range()
  {
    $s = new ClampSanitizer(min: 1, max: 100);
    $this->assertSame(50, $s->sanitize(50));
  }

  public function test_clamp_sanitizer_works_with_float_bounds()
  {
    $s = new ClampSanitizer(min: 1.0, max: 5.0);
    $this->assertSame(5.0, $s->sanitize(10.0));
    $this->assertSame(1.0, $s->sanitize(0.5));
  }

  public function test_clamp_sanitizer_passes_non_numeric_unchanged()
  {
    $s = new ClampSanitizer(min: 1, max: 100);
    $this->assertSame('abc', $s->sanitize('abc'));
  }

  public function test_clamp_sanitizer_processes_array()
  {
    $s = new ClampSanitizer(min: 1, max: 10);
    $this->assertSame([1, 10, 5], $s->sanitize([0, 20, 5]));
  }

  public function test_clamp_alias_resolves_via_handler_factory()
  {
    // alias uses ClampSanitizer with no bounds — value passes through
    $this->assertSame(200, Sanitizer::apply(200, 'clamp'));
  }

  // ─────────────────────────────────────────────
  // StripSpecialCharsSanitizer
  // ─────────────────────────────────────────────

  public function test_strip_special_chars_sanitizer_removes_special_characters()
  {
    $s = new StripSpecialCharsSanitizer;
    $this->assertSame('hello world', $s->sanitize('hello@#$ world!'));
  }

  public function test_strip_special_chars_sanitizer_allows_extra_chars()
  {
    $s = new StripSpecialCharsSanitizer(allowed: '_-');
    $this->assertSame('hello_world-2025', $s->sanitize('hello_world-2025!@#'));
  }

  public function test_strip_special_chars_sanitizer_uses_replacement()
  {
    $s = new StripSpecialCharsSanitizer(replacement: '*');
    $this->assertSame('hello*', $s->sanitize('hello!'));
  }

  public function test_strip_special_chars_sanitizer_processes_array()
  {
    $s = new StripSpecialCharsSanitizer;
    $this->assertSame(['hello', 'world'], $s->sanitize(['hello!', 'world@']));
  }

  public function test_strip_chars_alias_resolves_via_handler_factory()
  {
    $this->assertSame('hello', Sanitizer::apply('hello!', 'strip_chars'));
  }

  // ─────────────────────────────────────────────
  // Sanitizer::aliases() & extend()
  // ─────────────────────────────────────────────

  public function test_handler_factory_returns_all_built_in_aliases()
  {
    $aliases = Sanitizer::getAliases();

    $expected = [
      'trim',
      'lowercase',
      'uppercase',
      'strip_tags',
      'escape_html',
      'integer',
      'float',
      'boolean',
      'slug',
      'null_if_empty',
      'clamp',
      'strip_chars',
    ];

    foreach ($expected as $alias) {
      $this->assertArrayHasKey($alias, $aliases, "Missing alias: $alias");
    }
  }

  public function test_handler_factory_extend_registers_custom_alias()
  {
    Sanitizer::extend('my_trim', TrimSanitizer::class);

    $this->assertArrayHasKey('my_trim', Sanitizer::getAliases());
    $this->assertSame('hello', Sanitizer::apply('  hello  ', 'my_trim'));
  }
}
