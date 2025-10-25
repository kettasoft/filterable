<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Illuminate\Http\Request;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Engines\Tree;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Engines\Ruleset;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Kettasoft\Filterable\Engines\Factory\EngineManager;

class FilteringWithHeaderDrivenModeTest extends TestCase
{
  use RefreshDatabase;

  public function setUp(): void
  {
    parent::setUp();

    config()->set('filterable.header_driven_mode', [
      'header_name' => 'X-Filter-Mode',
      'enabled' => true,
      'allowed_engines' => [],
      'engine_map' => [],
      'fallback_strategy' => 'default',
    ]);
  }

  /**
   * It filtering with enable global header driven mode options.
   * @test
   */
  public function it_filtering_with_enable_global_header_driven_mode_options()
  {
    $engine = 'ruleset';

    $request = Request::capture();

    $request->headers->set('X-Filter-Mode', $engine);

    $filter = Filterable::withRequest($request);

    $this->assertEquals($engine, $request->header('X-Filter-Mode'));

    $this->assertInstanceOf(Ruleset::class, $filter->getEngine());
  }

  /**
   * it can filtering with allowed header driven mode engines only.
   * @test
   */
  public function it_can_filtering_with_allowed_header_driven_mode_engines_only()
  {
    $engine = 'tree';

    config()->set('filterable.header_driven_mode', [
      'enabled' => true,
      'header_name' => 'X-Filter-Mode',
      'allowed_engines' => ['tree']
    ]);

    $request = Request::capture();

    $request->headers->set('X-Filter-Mode', $engine);

    $filter = Filterable::withRequest($request);

    $this->assertEquals($engine, $request->header('X-Filter-Mode'));

    $this->assertInstanceOf(Tree::class, EngineManager::generate($engine, $filter));
  }

  /**
   * It can't filtering with not allowed header driven mode engines.
   * @test
   */
  public function it_cant_filtering_with_not_allowed_header_driven_mode_engines()
  {
    $engine = 'ruleset';

    config()->set('filterable.header_driven_mode', [
      'enabled' => true,
      'header_name' => 'X-Filter-Mode',
      'allowed_engines' => ['tree']
    ]);

    $request = Request::capture();

    $request->headers->set('X-Filter-Mode', $engine);

    $this->assertThrows(function () use ($request) {
      Filterable::withRequest($request);
    }, ValidationException::class);
  }

  /**
   * It can filtering with engine map name engine.
   * @test
   */
  public function it_can_filtering_with_engine_map_name_engine()
  {
    $engine = 'mobile';

    config()->set('filterable.header_driven_mode', [
      'enabled' => true,
      'header_name' => 'X-Filter-Mode',
      'engine_map' => ['mobile' => 'ruleset']
    ]);

    $request = Request::capture();

    $request->headers->set('X-Filter-Mode', $engine);

    $filter = Filterable::withRequest($request);

    $this->assertEquals($engine, $request->header('X-Filter-Mode'));

    $this->assertInstanceOf(Ruleset::class, $filter->getEngine());
  }

  /**
   * It use default engine when engine name not defined.
   * @test
   */
  public function it_use_default_engine_when_engine_name_not_defined()
  {
    $engine = 'mobile';

    config()->set('filterable.header_driven_mode', [
      'enabled' => true,
      'header_name' => 'X-Filter-Mode',
      'fallback_strategy' => 'default',
    ]);

    $request = Request::capture();

    $request->headers->set('X-Filter-Mode', $engine);

    $filter = Filterable::withRequest($request);

    $this->assertEquals($engine, $request->header('X-Filter-Mode'));

    $this->assertInstanceOf(get_class($filter->getEngine()), EngineManager::generate(config('filterable.default_engine'), $filter));
  }

  /**
   * it throw exception when engine is not define.
   * @test
   */
  public function it_throw_exception_when_engine_is_not_define()
  {
    $engine = 'mobile';

    config()->set('filterable.header_driven_mode', [
      'enabled' => true,
      'header_name' => 'X-Filter-Mode',
      'fallback_strategy' => 'error',
    ]);

    $request = Request::capture();

    $request->headers->set('X-Filter-Mode', $engine);

    $this->assertThrows(function () use ($request) {
      Filterable::withRequest($request);
    }, ValidationException::class);
  }

  /**
   * It use engine with custom header name.
   * @test
   */
  public function it_use_engine_with_custom_header_name()
  {
    $engine = 'tree';

    config()->set('filterable.header_driven_mode', [
      'enabled' => true,
      'header_name' => 'mode',
      'fallback_strategy' => 'error',
    ]);

    $request = Request::capture();

    $request->headers->set('mode', $engine);

    $filter = Filterable::withRequest($request);

    $this->assertEquals($engine, $request->header('mode'));

    $this->assertInstanceOf(Tree::class, EngineManager::generate($engine, $filter));
  }

  /**
   * it use header driven mode per request only.
   * @test
   */
  public function it_use_header_driven_mode_per_request_only()
  {
    $engine = 'tree';

    config()->set('filterable.header_driven_mode', [
      'header_name' => 'X-Filter-Mode',
      'enabled' => false,
      'allowed_engines' => [],
      'engine_map' => [],
      'fallback_strategy' => 'default',
    ]);

    $request = Request::capture();

    $request->headers->set('mode', $engine);

    $filter = Filterable::withRequest($request)->withHeaderDrivenMode();

    $this->assertEquals($engine, $request->header('mode'));

    $this->assertInstanceOf(Tree::class, EngineManager::generate($engine, $filter));
  }
}
