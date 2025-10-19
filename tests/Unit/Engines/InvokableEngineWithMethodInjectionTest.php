<?php

namespace Kettasoft\Filterable\Tests\Unit\Engines;

use Illuminate\Http\Request;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Engines\Invokable;
use Kettasoft\Filterable\Support\Payload;

class InvokableEngineWithMethodInjectionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Invokable::injectGlobalMethod('withTrashed', function ($payload) {
            return $this->builder->where('deleted_at', '!=', null);
        });
    }
    public function test_it_can_inject_methods_into_invokable_engine()
    {
        $request = request()->merge([
            'with_trashed' => 'true',
        ]);

        $filter = Filterable::create($request)->tap(function (Filterable $filterable) {
            $filterable->setFilters(['with_trashed']);
            $filterable->useEngin(Invokable::class);
        });

        $invoker = Post::filter($filter);

        $this->assertTrue(Invokable::hasInjectedMethod('withTrashed'));
        $this->assertStringContainsString('"deleted_at" is not null', $invoker->toSql());
    }
}
