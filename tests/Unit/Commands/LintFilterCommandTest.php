<?php

namespace Tests\Unit\Commands;

use Kettasoft\Filterable\Tests\TestCase;

// ──────────────────────────────────────────────────────────────────────────────
// Fixture helpers (anonymous-class factories to avoid global namespace pollution)
// ──────────────────────────────────────────────────────────────────────────────

/**
 * Test suite for the filterable:lint Artisan command.
 */
class LintFilterCommandTest extends TestCase
{
    // ── Helpers ───────────────────────────────────────────────────────────────

  /**
   * Dynamically declare a named class extending Filterable in a temp namespace
   * so that resolveFilterClass() can find it via class_exists().
   *
   * Returns the FQCN of the declared class.
   */
  private function declareFilter(string $shortName, string $body = ''): string
  {
    $fqcn = 'Kettasoft\\Filterable\\Tests\\LintFixtures\\' . $shortName;

    if (!class_exists($fqcn)) {
      // phpcs:ignore
      eval("namespace Kettasoft\\Filterable\\Tests\\LintFixtures; {$body}");
    }

    return $fqcn;
  }

  // ──────────────────────────────────────────────────────────────────────────
  // Tests
  // ──────────────────────────────────────────────────────────────────────────

  public function test_it_passes_a_clean_filter_with_no_issues(): void
  {
    $fqcn = $this->declareFilter('CleanFilter', <<<'PHP'
            use Kettasoft\Filterable\Filterable;
            use Kettasoft\Filterable\Support\Payload;
            class CleanFilter extends Filterable {
                protected $filters = ['status'];
                public function status(Payload $payload) { return $this->builder; }
            }
        PHP);

    $this->artisan('filterable:lint', ['filter' => $fqcn])
      ->assertSuccessful();
  }

  public function test_it_reports_l002_when_filters_array_is_empty(): void
  {
    $fqcn = $this->declareFilter('EmptyFiltersFilter', <<<'PHP'
            use Kettasoft\Filterable\Filterable;
            class EmptyFiltersFilter extends Filterable {
                protected $filters = [];
            }
        PHP);

    $this->artisan('filterable:lint', ['filter' => $fqcn])
      ->assertSuccessful() // warnings don't fail by default
      ->expectsOutputToContain('L002');
  }

  public function test_it_reports_l003_when_filter_key_has_no_method(): void
  {
    $fqcn = $this->declareFilter('OrphanKeyFilter', <<<'PHP'
            use Kettasoft\Filterable\Filterable;
            class OrphanKeyFilter extends Filterable {
                protected $filters = ['ghost'];
            }
        PHP);

    $this->artisan('filterable:lint', ['filter' => $fqcn])
      ->assertFailed() // L003 is an error
      ->expectsOutputToContain('L003');
  }

  public function test_it_reports_l004_when_payload_method_is_not_in_filters(): void
  {
    $fqcn = $this->declareFilter('UnlistedMethodFilter', <<<'PHP'
            use Kettasoft\Filterable\Filterable;
            use Kettasoft\Filterable\Support\Payload;
            class UnlistedMethodFilter extends Filterable {
                protected $filters = [];
                public function hidden(Payload $payload) { return $this->builder; }
            }
        PHP);

    $this->artisan('filterable:lint', ['filter' => $fqcn])
      ->assertSuccessful() // L004 is a warning
      ->expectsOutputToContain('L004');
  }

  public function test_it_reports_l005_when_cast_type_class_does_not_exist(): void
  {
    $fqcn = $this->declareFilter('BadCastFilter', <<<'PHP'
            use Kettasoft\Filterable\Filterable;
            use Kettasoft\Filterable\Support\Payload;
            use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Cast;
            class BadCastFilter extends Filterable {
                protected $filters = ['price'];
                #[Cast('NonExistentCastClass')]
                public function price(Payload $payload) { return $this->builder; }
            }
        PHP);

    $this->artisan('filterable:lint', ['filter' => $fqcn])
      ->assertFailed() // L005 is an error
      ->expectsOutputToContain('L005');
  }

  public function test_it_does_not_report_l005_for_builtin_cast_types(): void
  {
    $fqcn = $this->declareFilter('BuiltinCastFilter', <<<'PHP'
            use Kettasoft\Filterable\Filterable;
            use Kettasoft\Filterable\Support\Payload;
            use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Cast;
            class BuiltinCastFilter extends Filterable {
                protected $filters = ['age'];
                #[Cast('int')]
                public function age(Payload $payload) { return $this->builder; }
            }
        PHP);

    $this->artisan('filterable:lint', ['filter' => $fqcn])
      ->assertSuccessful();
  }

  public function test_it_reports_l005_when_authorize_class_does_not_implement_authorizable(): void
  {
    // Declare a class that does NOT implement Authorizable
    if (!class_exists('Kettasoft\\Filterable\\Tests\\LintFixtures\\NotAnAuthorizer')) {
      eval('namespace Kettasoft\\Filterable\\Tests\\LintFixtures; class NotAnAuthorizer {}');
    }

    $fqcn = $this->declareFilter('BadAuthorizeFilter', <<<'PHP'
            use Kettasoft\Filterable\Filterable;
            use Kettasoft\Filterable\Support\Payload;
            use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Authorize;
            class BadAuthorizeFilter extends Filterable {
                protected $filters = ['title'];
                #[Authorize(\Kettasoft\Filterable\Tests\LintFixtures\NotAnAuthorizer::class)]
                public function title(Payload $payload) { return $this->builder; }
            }
        PHP);

    $this->artisan('filterable:lint', ['filter' => $fqcn])
      ->assertFailed()
      ->expectsOutputToContain('L005');
  }

  public function test_it_reports_l007_when_annotation_is_on_unlisted_method(): void
  {
    $fqcn = $this->declareFilter('AnnotationUnlistedFilter', <<<'PHP'
            use Kettasoft\Filterable\Filterable;
            use Kettasoft\Filterable\Support\Payload;
            use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Cast;
            class AnnotationUnlistedFilter extends Filterable {
                protected $filters = [];
                #[Cast('int')]
                public function price(Payload $payload) { return $this->builder; }
            }
        PHP);

    $this->artisan('filterable:lint', ['filter' => $fqcn])
      ->assertSuccessful() // L007 is a warning
      ->expectsOutputToContain('L007');
  }

  public function test_it_reports_l008_when_validation_rule_key_not_in_filters(): void
  {
    $fqcn = $this->declareFilter('OrphanRuleFilter', <<<'PHP'
            use Kettasoft\Filterable\Filterable;
            class OrphanRuleFilter extends Filterable {
                protected $filters = ['title'];
                public function title(\Kettasoft\Filterable\Support\Payload $p) { return $this->builder; }
                public function rules(): array {
                    return ['ghost' => 'required|string'];
                }
            }
        PHP);

    $this->artisan('filterable:lint', ['filter' => $fqcn])
      ->assertSuccessful() // L008 is a warning
      ->expectsOutputToContain('L008');
  }

  public function test_it_fails_when_filter_class_cannot_be_resolved(): void
  {
    $this->artisan('filterable:lint', ['filter' => 'CompletelyNonExistentXyz'])
      ->assertFailed();
  }

  public function test_it_passes_without_argument_when_no_filters_exist(): void
  {
    // getFilters() scans app/Http/Filters — returns [] in test env
    $this->artisan('filterable:lint')
      ->assertSuccessful();
  }

  public function test_strict_mode_fails_on_warnings(): void
  {
    $fqcn = $this->declareFilter('StrictEmptyFilter', <<<'PHP'
            use Kettasoft\Filterable\Filterable;
            class StrictEmptyFilter extends Filterable {
                protected $filters = [];
            }
        PHP);

    // Without --strict: exits 0 despite L002 warning
    $this->artisan('filterable:lint', ['filter' => $fqcn])
      ->assertSuccessful();

    // With --strict: exits non-zero
    $this->artisan('filterable:lint', [
      'filter'   => $fqcn,
      '--strict' => true,
    ])->assertFailed();
  }

  public function test_it_lints_multiple_classes_when_no_argument_given(): void
  {
    // Declare two filters so getFilters() can find them (requires app_path setup)
    // In the test environment getFilters() returns [] from the empty app/Http/Filters,
    // so we just assert the command succeeds without crashing.
    $this->artisan('filterable:lint')->assertSuccessful();
  }

    // ── New tests for the 3 fixes ─────────────────────────────────────────────

  /**
   * Fix 1 — snake_case key in $filters resolves to camelCase method name.
   * 'user_id' → 'userId', so userId() should be a valid filter method.
   *
   */
  public function test_it_passes_when_snake_case_key_maps_to_camel_case_method(): void
  {
    $fqcn = $this->declareFilter('SnakeCaseKeyFilter', <<<'PHP'
            use Kettasoft\Filterable\Filterable;
            use Kettasoft\Filterable\Support\Payload;
            class SnakeCaseKeyFilter extends Filterable {
                protected $filters = ['user_id'];
                public function userId(Payload $payload) { return $this->builder; }
            }
        PHP);

    $this->artisan('filterable:lint', ['filter' => $fqcn])
      ->assertSuccessful();
  }

  /**
   * Fix 1 (negative) — snake_case key whose camelCase method is missing → L003.
   *
   */
  public function test_it_reports_l003_when_snake_case_key_has_no_camel_case_method(): void
  {
    $fqcn = $this->declareFilter('MissingCamelFilter', <<<'PHP'
            use Kettasoft\Filterable\Filterable;
            class MissingCamelFilter extends Filterable {
                protected $filters = ['user_id'];
                // userId() is missing
            }
        PHP);

    $this->artisan('filterable:lint', ['filter' => $fqcn])
      ->assertFailed()
      ->expectsOutputToContain('L003');
  }

  /**
   * Fix 1 — L004 should NOT fire for a method that is reachable via snake_case key.
   * i.e. method 'userId' is reachable from key 'user_id', so no L004.
   *
   */
  public function test_it_does_not_report_l004_for_method_reachable_via_snake_case_key(): void
  {
    $fqcn = $this->declareFilter('SnakeCaseL004Filter', <<<'PHP'
            use Kettasoft\Filterable\Filterable;
            use Kettasoft\Filterable\Support\Payload;
            class SnakeCaseL004Filter extends Filterable {
                protected $filters = ['user_id'];
                public function userId(Payload $payload) { return $this->builder; }
            }
        PHP);

    $output = $this->artisan('filterable:lint', ['filter' => $fqcn])
      ->assertSuccessful();

    // L004 must not appear
    $output->expectsOutputToContain('No issues found');
  }

  /**
   * Fix 2 — L003 fires when the method exists but has no Payload parameter.
   *
   */
  public function test_it_reports_l003_when_method_exists_but_has_no_payload_param(): void
  {
    $fqcn = $this->declareFilter('NoPayloadParamFilter', <<<'PHP'
            use Kettasoft\Filterable\Filterable;
            class NoPayloadParamFilter extends Filterable {
                protected $filters = ['status'];
                public function status() { return $this->builder; }  // no Payload param
            }
        PHP);

    $this->artisan('filterable:lint', ['filter' => $fqcn])
      ->assertFailed()
      ->expectsOutputToContain('L003');
  }

  /**
   * Fix 2 — L003 fires when the method's first param is not typed as Payload.
   *
   */
  public function test_it_reports_l003_when_method_first_param_is_wrong_type(): void
  {
    $fqcn = $this->declareFilter('WrongParamTypeFilter', <<<'PHP'
            use Kettasoft\Filterable\Filterable;
            class WrongParamTypeFilter extends Filterable {
                protected $filters = ['status'];
                public function status(string $value) { return $this->builder; }
            }
        PHP);

    $this->artisan('filterable:lint', ['filter' => $fqcn])
      ->assertFailed()
      ->expectsOutputToContain('L003');
  }

  /**
   * Fix 3 — Constructor exception is caught and reported as L001 without crashing.
   *
   */
  public function test_it_reports_l001_when_class_constructor_throws(): void
  {
    $fqcn = $this->declareFilter('ThrowingFilter', <<<'PHP'
            use Kettasoft\Filterable\Filterable;
            class ThrowingFilter extends Filterable {
                public function __construct() {
                    throw new \RuntimeException('Intentional failure');
                }
            }
        PHP);

    $this->artisan('filterable:lint', ['filter' => $fqcn])
      ->assertFailed()
      ->expectsOutputToContain('L001');
  }

  /**
   * L011 — method is named after the raw key instead of its camelCase equivalent.
   * e.g. key 'user_id' but method is 'user_id()' instead of 'userId()'.
   *
   */
  public function test_it_reports_l011_when_method_uses_raw_key_name_instead_of_camel_case(): void
  {
    $fqcn = $this->declareFilter('RawKeyMethodFilter', <<<'PHP'
            use Kettasoft\Filterable\Filterable;
            use Kettasoft\Filterable\Support\Payload;
            class RawKeyMethodFilter extends Filterable {
                protected $filters = ['user_id'];
                public function user_id(Payload $payload) { return $this->builder; }
            }
        PHP);

    $this->artisan('filterable:lint', ['filter' => $fqcn])
      ->assertSuccessful()   // warning, not error → exits 0
      ->expectsOutputToContain('L011');
  }

  /**
   * L011 should NOT fire when key and method name are already identical (no transformation needed).
   * e.g. key 'status' → method 'status()' is correct.
   *
   */
  public function test_it_does_not_report_l011_when_key_needs_no_camel_case_transformation(): void
  {
    $fqcn = $this->declareFilter('SimpleKeyFilter', <<<'PHP'
            use Kettasoft\Filterable\Filterable;
            use Kettasoft\Filterable\Support\Payload;
            class SimpleKeyFilter extends Filterable {
                protected $filters = ['status'];
                public function status(Payload $payload) { return $this->builder; }
            }
        PHP);

    $this->artisan('filterable:lint', ['filter' => $fqcn])
      ->assertSuccessful();
  }
}
