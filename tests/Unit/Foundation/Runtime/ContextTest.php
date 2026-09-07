<?php

namespace Kettasoft\Filterable\Tests\Unit\Foundation\Runtime;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Exceptions\MissingBuilderException;
use Kettasoft\Filterable\Foundation\Caching\CacheKeyGenerator;
use Kettasoft\Filterable\Foundation\Runtime\Context;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;

class ContextTest extends TestCase
{
  public function test_it_manages_data_builder_and_cache_generator()
  {
    $context = new Context;
    $builder = Post::query();
    $generator = new CacheKeyGenerator;

    $this->assertFalse($context->hasBuilder());
    $this->assertNull($context->getBuilder());
    $this->assertNull($context->getCacheKeyGenerator());

    $context->setData(['status' => 'active']);
    $context->setBuilder($builder);
    $context->setCacheKeyGenerator($generator);

    $this->assertSame(['status' => 'active'], $context->getData());
    $this->assertSame($builder, $context->getBuilder());
    $this->assertTrue($context->hasBuilder());
    $this->assertSame($generator, $context->getCacheKeyGenerator());
  }

  public function test_it_stores_payload_snapshots()
  {
    $context = new Context;
    $applied = Payload::create('status', '=', 'active', 'active');
    $skipped = Payload::create('title', '=', 'invalid', 'invalid');

    $context->commitPayload('status', $applied);
    $context->skipPayload($skipped, 'Invalid title');

    $applied->setValue('changed');
    $skipped->setValue('changed');

    $this->assertSame('active', $context->getApplied('status')->value);
    $this->assertSame('invalid', $context->getSkipped('title')[0]['payload']->value);
    $this->assertTrue($context->hasSkipped('title'));
    $this->assertFalse($context->hasSkipped('missing'));
  }

  public function test_cloned_filters_have_isolated_runtime_state_and_engines()
  {
    $original = new Filterable;
    $original->setData(['status' => 'original']);

    $clone = clone $original;
    $clone->setData(['status' => 'clone']);
    $clone->skip(
      Payload::create('status', '=', 'invalid', 'invalid'),
      'Invalid status'
    );

    $this->assertSame(['status' => 'original'], $original->getData());
    $this->assertSame(['status' => 'clone'], $clone->getData());
    $this->assertFalse($original->hasSkipped('status'));
    $this->assertTrue($clone->hasSkipped('status'));
    $this->assertSame($original, $original->getEngine()->getContext());
    $this->assertSame($clone, $clone->getEngine()->getContext());
    $this->assertNotSame($original->getEngine(), $clone->getEngine());
  }

  public function test_filterable_throws_a_domain_exception_when_builder_is_missing()
  {
    $this->expectException(MissingBuilderException::class);

    (new Filterable)->getBuilder();
  }

  public function test_builder_context_tracks_initial_and_final_builder_replacements()
  {
    $source = Post::query();

    $filter = new class extends Filterable {
      public ?Builder $initialBuilder = null;

      public ?Builder $finalBuilder = null;

      protected function initially(Builder $builder): Builder
      {
        return $this->initialBuilder = clone $builder;
      }

      protected function finally(Builder $builder): Builder
      {
        return $this->finalBuilder = clone $builder;
      }
    };

    $result = $filter->shouldReturnQueryBuilder()->apply($source);

    $this->assertNotSame($source, $filter->initialBuilder);
    $this->assertNotSame($filter->initialBuilder, $filter->finalBuilder);
    $this->assertSame($filter->finalBuilder, $result);
    $this->assertSame($result, $filter->getBuilder());
  }

  public function test_validation_uses_complete_runtime_data_when_a_filter_key_is_set()
  {
    request()->merge([
      'filter' => ['status' => 'active'],
      'token' => 'present',
    ]);

    $filter = new class extends Filterable {
      protected $filters = [];

      public function rules(): array
      {
        return ['token' => ['required']];
      }
    };

    $filter->shouldReturnQueryBuilder()->apply(Post::query());

    $this->assertSame(['status' => 'active'], $filter->getData());
  }

  public function test_cache_keys_include_complete_runtime_data()
  {
    $makeFilter = function (int $tenantId) {
      return new class($tenantId) extends Filterable {
        protected $filters = [];

        public function __construct(int $tenantId)
        {
          parent::__construct();
          $this->setData([
            'filter' => ['status' => 'active'],
            'tenant_id' => $tenantId,
          ]);
          $this->setBuilder(Post::query());
        }

        public function exposedCacheKey(): string
        {
          return $this->generateCacheKey();
        }
      };
    };

    $this->assertNotSame(
      $makeFilter(1)->exposedCacheKey(),
      $makeFilter(2)->exposedCacheKey()
    );
  }
}
