<?php

namespace Kettasoft\Filterable\Tests\Feature\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Kettasoft\Filterable\Support\Stub;
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

    config()->set('filterable.generator.stubs', __DIR__ . '/../../../stubs/');
  }

  /**
   * Clean up the testing environment before the next test.
   *
   * @return void
   */
  protected function tearDown(): void
  {
    File::deleteDirectory(config('filterable.save_filters_at'));

    parent::tearDown();
  }

  /**
   * It creates basic filter file.
   * @test
   */
  public function it_creates_basic_filter_file()
  {
    $filename = 'UserFilter';

    $result = Artisan::call("filterable:make-filter", [
      "name" => $filename
    ]);

    $this->assertEquals(1, $result);
    $this->assertTrue(File::exists(app_path('Http/Filters') . "/$filename.php"));
  }

  /**
   * It creates filter with methods file
   * @test
   */
  public function it_creates_filter_with_methods_file()
  {
    $filename = 'UserFilter';

    $result = Artisan::call("filterable:make-filter", [
      "name" => $filename,
      '--filters' => 'methods'
    ]);

    $this->assertEquals(1, $result);
    $this->assertTrue(File::exists(app_path('Http/Filters') . "/$filename.php"));
  }
}
