<?php

namespace Tests\Unit\Commands;

use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Support\Stub;

/**
 * Test suite for the filterable:add-method Artisan command.
 */
class AddMethodCommandTest extends TestCase
{
  /** Temporary directory used across tests */
  private string $tmpDir;

  /** Path to the temp filter file written for each test */
  private string $tmpFile;

  /** Fully qualified class name of the dynamically generated filter */
  private string $filterClass;

  /** Minimal filter class source written to disk before each test */
  private const BASE_SOURCE = <<<'PHP'
<?php

namespace Kettasoft\Filterable\Tests\TmpFilters;

use Illuminate\Database\Eloquent\Builder;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;

class TmpFilter extends Filterable
{
    protected $filters = ['status', 'title'];

    public function status(Payload $payload)
    {
        if ($payload->value) {
            return $this->builder->where('status', $payload->operator, $payload->value);
        }

        return $this->builder;
    }

    public function title(Payload $payload)
    {
        if ($payload->value) {
            return $this->builder->where('title', $payload->operator, $payload->value);
        }

        return $this->builder;
    }
}
PHP;

  // ──────────────────────────────────────────────────────────────────────────
  // Lifecycle
  // ──────────────────────────────────────────────────────────────────────────

  protected function getEnvironmentSetUp($app): void
  {
    parent::getEnvironmentSetUp($app);

    // Point the stubs config to the real stubs directory inside this package
    $app['config']->set(
      'filterable.generator.stubs',
      dirname(__DIR__, 3) . '/stubs/'
    );
  }

  public function setUp(): void
  {
    parent::setUp();

    // Point Stub to the real stubs directory so method.stub can be found
    Stub::setBasePath(dirname(__DIR__, 3) . '/stubs/');

    // Create a temporary directory that mirrors the namespace
    $this->tmpDir  = sys_get_temp_dir() . '/filterable_tests/TmpFilters';
    @mkdir($this->tmpDir, 0755, true);

    $this->tmpFile     = $this->tmpDir . '/TmpFilter.php';
    $this->filterClass = 'Kettasoft\\Filterable\\Tests\\TmpFilters\\TmpFilter';

    // Write the base source file
    file_put_contents($this->tmpFile, self::BASE_SOURCE);

    // Load the class so PHP knows about it
    $this->loadTmpClass();
  }

  public function tearDown(): void
  {
    // Remove the temp file (not the directory, other test runs may use it)
    if (file_exists($this->tmpFile)) {
      @unlink($this->tmpFile);
    }

    parent::tearDown();
  }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

  /**
   * Require the temp class file if the class has not been loaded yet.
   * We use class_alias to map the short-form name to the FQCN so
   * resolveFilterClass() can locate it via class_exists().
   */
  private function loadTmpClass(): void
  {
    if (!class_exists($this->filterClass)) {
      require $this->tmpFile;
    }
  }

  /**
   * Re-read the file content from disk (reflects what the command wrote).
   */
  private function content(): string
  {
    return file_get_contents($this->tmpFile);
  }

  // ──────────────────────────────────────────────────────────────────────────
  // Tests
  // ──────────────────────────────────────────────────────────────────────────

  public function test_it_requires_the_name_option(): void
  {
    $this->artisan('filterable:add-method', [
      'filter' => $this->filterClass,
    ])->assertFailed();
  }

  public function test_it_fails_when_name_is_empty(): void
  {
    $this->artisan('filterable:add-method', [
      'filter'   => $this->filterClass,
      '--name'   => '',
    ])->assertFailed();
  }

  public function test_it_fails_for_invalid_method_name(): void
  {
    $this->artisan('filterable:add-method', [
      'filter' => $this->filterClass,
      '--name' => '1invalid',
    ])->assertFailed();

    $this->artisan('filterable:add-method', [
      'filter' => $this->filterClass,
      '--name' => 'has-hyphen',
    ])->assertFailed();
  }

  public function test_it_fails_when_filter_class_is_not_found(): void
  {
    $this->artisan('filterable:add-method', [
      'filter' => 'NonExistentFilter',
      '--name' => 'someMethod',
    ])->assertFailed();
  }

  public function test_it_adds_a_new_method_at_the_end_of_the_class(): void
  {
    $this->artisan('filterable:add-method', [
      'filter' => $this->filterClass,
      '--name' => 'isActive',
    ])->assertSuccessful();

    $content = $this->content();

    $this->assertStringContainsString('function isActive(', $content);
  }

  public function test_it_inserts_after_a_specified_existing_method(): void
  {
    $this->artisan('filterable:add-method', [
      'filter'  => $this->filterClass,
      '--name'  => 'category',
      '--after' => 'status',
    ])->assertSuccessful();

    $content = $this->content();

    $this->assertStringContainsString('function category(', $content);

    // category should appear before title in the file
    $posCategory = strpos($content, 'function category(');
    $posTitle    = strpos($content, 'function title(');
    $this->assertLessThan($posTitle, $posCategory, 'category should be inserted before title');
  }

  public function test_it_fails_when_after_method_does_not_exist(): void
  {
    $this->artisan('filterable:add-method', [
      'filter'  => $this->filterClass,
      '--name'  => 'newMethod',
      '--after' => 'nonExistentMethod',
    ])->assertFailed();

    // File must not be modified
    $this->assertStringNotContainsString('function newMethod(', $this->content());
  }

  public function test_it_does_not_duplicate_an_existing_method(): void
  {
    $this->artisan('filterable:add-method', [
      'filter' => $this->filterClass,
      '--name' => 'status',    // already exists
    ])->assertSuccessful();      // warns but exits 0

    // Count occurrences — there should still be exactly one
    $count = substr_count($this->content(), 'function status(');
    $this->assertSame(1, $count);
  }

  public function test_it_adds_method_name_to_filters_array(): void
  {
    $this->artisan('filterable:add-method', [
      'filter' => $this->filterClass,
      '--name' => 'publishedAt',
    ])->assertSuccessful();

    $this->assertStringContainsString("'publishedAt'", $this->content());
  }

  public function test_it_does_not_duplicate_key_in_filters_array(): void
  {
    $this->artisan('filterable:add-method', [
      'filter' => $this->filterClass,
      '--name' => 'status',   // already in the $filters array
    ])->assertSuccessful();

    $content = $this->content();

    // Extract the $filters = [...] declaration and check 'status' appears once there
    preg_match('/\$filters\s*=\s*\[([^\]]*)\]/s', $content, $m);
    $filtersDecl = $m[1] ?? '';

    $count = substr_count($filtersDecl, "'status'");
    $this->assertSame(1, $count, "'status' should not be duplicated in the \$filters array");
  }

  public function test_generated_method_uses_correct_stub_placeholder(): void
  {
    $this->artisan('filterable:add-method', [
      'filter' => $this->filterClass,
      '--name' => 'sortBy',
    ])->assertSuccessful();

    $content = $this->content();

    // The stub replaces $$NAME$$ with the provided name in the where() call
    $this->assertStringContainsString("'sortBy'", $content);
    $this->assertStringContainsString('function sortBy(Payload $payload)', $content);
  }

  public function test_it_inserts_new_method_before_last_closing_brace_when_no_after(): void
  {
    $this->artisan('filterable:add-method', [
      'filter' => $this->filterClass,
      '--name' => 'slug',
    ])->assertSuccessful();

    $content = $this->content();

    // The class must still be syntactically closed
    $this->assertStringEndsWith("}\n", $content);
  }
}
