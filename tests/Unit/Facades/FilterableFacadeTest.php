<?php

namespace Kettasoft\Filterable\Tests\Unit\Facades;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Kettasoft\Filterable\Facades\Filterable;
use Kettasoft\Filterable\Foundation\FilterableSettings;
use Kettasoft\Filterable\Foundation\Resources;
use Kettasoft\Filterable\Tests\TestCase;

class FilterableFacadeTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->app->instance(Request::class, Request::create('/test', 'GET'));
    }

    /** @test */
    public function it_can_create_new_instance()
    {
        $filterable = Filterable::create();
        $this->assertInstanceOf(\Kettasoft\Filterable\Filterable::class, $filterable);
    }

    /** @test */
    public function it_can_create_instance_with_custom_request()
    {
        $request = Request::create('/custom', 'GET');
        $filterable = Filterable::withRequest($request);
        
        $this->assertInstanceOf(\Kettasoft\Filterable\Filterable::class, $filterable);
        $this->assertEquals($request, $filterable->getRequest());
    }

    /** @test */
    public function it_can_get_resources_instance()
    {
        $resources = Filterable::getResources();
        $this->assertInstanceOf(Resources::class, $resources);
    }

    /** @test */
    public function it_can_get_settings_instance()
    {
        $settings = Filterable::settings();
        $this->assertInstanceOf(FilterableSettings::class, $settings);
    }

    /** @test */
    public function it_can_enable_strict_mode()
    {
        $filterable = Filterable::strict();
        $this->assertTrue($filterable->isStrict());
    }

    /** @test */
    public function it_can_enable_permissive_mode()
    {
        $filterable = Filterable::permissive();
        $this->assertFalse($filterable->isStrict());
    }

    /** @test */
    public function it_can_set_and_get_allowed_fields()
    {
        $fields = ['name', 'email', 'age'];
        $filterable = Filterable::setAllowedFields($fields);
        
        $this->assertEquals($fields, $filterable->getAllowedFields());
    }

    /** @test */
    public function it_can_ignore_empty_values()
    {
        $filterable = Filterable::ignoreEmptyValues();
        $this->assertTrue($filterable->hasIgnoredEmptyValues());
    }

    /** @test */
    public function it_can_set_and_get_data()
    {
        $data = ['name' => 'John', 'age' => 25];
        $filterable = Filterable::setData($data);
        
        $this->assertEquals($data, $filterable->getData());
    }

    /** @test */
    public function it_can_set_and_get_model()
    {
        $modelClass = '\Kettasoft\Filterable\Tests\Models\Post';
        $filterable = Filterable::setModel($modelClass);
        
        $this->assertEquals($modelClass, $filterable->getModel());
    }

    /** @test */
    public function it_can_apply_conditional_logic()
    {
        $condition = true;
        $called = false;

        $filterable = Filterable::when($condition, function($filter) use (&$called) {
            $called = true;
            return $filter;
        });

        $this->assertTrue($called);
        $this->assertInstanceOf(\Kettasoft\Filterable\Filterable::class, $filterable);
    }

    /** @test */
    public function it_can_add_and_get_sorting()
    {
        $filterClass = 'TestFilter';
        $callback = function($query) { return $query; };
        
        Filterable::addSorting($filterClass, $callback);
        $sorting = Filterable::getSorting($filterClass);
        
        $this->assertNotNull($sorting);
    }

    /** @test */
    public function it_can_set_and_get_fields_map()
    {
        $map = ['display_name' => 'name', 'user_email' => 'email'];
        $filterable = Filterable::setFieldsMap($map);
        
        $this->assertEquals($map, $filterable->getFieldsMap());
    }

    /** @test */
    public function it_can_convert_query_to_sql()
    {
        $builder = \Kettasoft\Filterable\Tests\Models\Post::query();
        
        $sql = Filterable::setBuilder($builder)->toSql();
        
        $this->assertEquals('select * from "posts"', strtolower($sql));
    }

    /** @test */
    public function it_can_enable_header_driven_mode()
    {
        $config = ['header' => 'X-Filter-Engine'];
        $filterable = Filterable::withHeaderDrivenMode($config);
        
        $this->assertInstanceOf(\Kettasoft\Filterable\Filterable::class, $filterable);
    }

    /** @test */
    public function it_can_disable_sanitizers()
    {
        $filterable = Filterable::withoutSanitizers();
        $this->assertInstanceOf(\Kettasoft\Filterable\Filterable::class, $filterable);
    }
}