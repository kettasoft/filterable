<?php

namespace Kettasoft\Filterable\Tests\Feature\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Kettasoft\Filterable\Tests\TestCase;

class MakeFilterCommandTest extends TestCase
{

  /**
   * Setup the test environment.
   *
   * @return void
   */
  public function setUp(): void
  {
    parent::setUp();

    config()->set('filterable.namespace', 'App\\Http\\Filters');
    config()->set('filterable.save_filters_at', base_path('tests/tmp/Filters'));
    config()->set('filterable.generator.stubs', __DIR__ . '/../../../stubs/');
  }

  /**
   * Clean up the testing environment before the next test.
   *
   * @return void
   */
  protected function tearDown(): void
  {
    File::deleteDirectory(base_path('tests/tmp'));

    parent::tearDown();
  }

  /**
   * It creates basic filter file.
   * @test
   */
  public function it_creates_basic_filter_file()
  {
    $filename = 'UserFilter';
    $filePath = base_path("tests/tmp/Filters/{$filename}.php");

    $result = Artisan::call("filterable:make-filter", [
      "name" => $filename
    ]);

    $this->assertEquals(Command::SUCCESS, $result);
    $this->assertTrue(File::exists($filePath));
    $this->assertStringContainsString('namespace App\\Http\\Filters;', File::get($filePath));
  }

  /**
   * It creates filter with methods file
   * @test
   */
  public function it_creates_filter_with_methods_file()
  {
    $filename = 'UserFilter';
    $filePath = base_path("tests/tmp/Filters/{$filename}.php");

    $result = Artisan::call("filterable:make-filter", [
      "name" => $filename,
      '--filters' => 'methods'
    ]);

    $this->assertEquals(Command::SUCCESS, $result);
    $this->assertTrue(File::exists($filePath));
    $this->assertStringContainsString("public function methods(Payload \$payload)", File::get($filePath));
  }

  /**
   * It creates filter file using custom path and namespace options.
   * @test
   */
  public function it_creates_filter_file_using_custom_path_and_namespace_options()
  {
    $filename = 'BlogPostFilter';
    $relativePath = 'tests/tmp/Modules/Blog/app/Filters';
    $namespace = 'Modules\\Blog\\App\\Filters';
    $filePath = base_path("{$relativePath}/{$filename}.php");

    $result = Artisan::call("filterable:make-filter", [
      "name" => $filename,
      '--path' => $relativePath,
      '--namespace' => $namespace,
    ]);

    $this->assertEquals(Command::SUCCESS, $result);
    $this->assertTrue(File::exists($filePath));
    $this->assertStringContainsString("namespace {$namespace};", File::get($filePath));
  }

  /**
   * It normalizes forward slashes in a custom namespace.
   * @test
   */
  public function it_normalizes_custom_namespace_separators()
  {
    $filePath = base_path('tests/tmp/Modules/Blog/SlashFilter.php');

    $result = Artisan::call('filterable:make-filter', [
      'name' => 'SlashFilter',
      '--path' => 'tests/tmp/Modules/Blog',
      '--namespace' => 'Modules/Blog/Filters',
    ]);

    $this->assertEquals(Command::SUCCESS, $result);
    $this->assertStringContainsString(
      'namespace Modules\\Blog\\Filters;',
      File::get($filePath)
    );
  }

  /**
   * It rejects a namespace that would generate invalid PHP.
   * @test
   */
  public function it_rejects_an_invalid_custom_namespace()
  {
    $result = Artisan::call('filterable:make-filter', [
      'name' => 'InvalidNamespaceFilter',
      '--path' => 'tests/tmp/Invalid',
      '--namespace' => 'Modules/Invalid-Namespace/Filters',
    ]);

    $this->assertEquals(Command::FAILURE, $result);
    $this->assertStringContainsString('is not valid', Artisan::output());
    $this->assertFalse(File::exists(base_path('tests/tmp/Invalid/InvalidNamespaceFilter.php')));
  }

  /**
   * It rejects a class name that would generate invalid PHP.
   * @test
   */
  public function it_rejects_an_invalid_filter_class_name()
  {
    $result = Artisan::call('filterable:make-filter', [
      'name' => '123InvalidFilter',
    ]);

    $this->assertEquals(Command::FAILURE, $result);
    $this->assertStringContainsString('class name [123InvalidFilter] is not valid', Artisan::output());
    $this->assertFalse(File::exists(base_path('tests/tmp/Filters/123InvalidFilter.php')));
  }
}
