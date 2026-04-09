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
}
