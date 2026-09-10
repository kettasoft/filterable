<?php

namespace Kettasoft\Filterable\Tests\Unit\Drivers;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Kettasoft\Filterable\Drivers\Contracts\Driver;
use Kettasoft\Filterable\Drivers\DatabaseDriver;
use Kettasoft\Filterable\Exceptions\InvalidDriverResultException;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Operations\Comparison;
use Kettasoft\Filterable\Operations\Contracts\Operation;
use Kettasoft\Filterable\Operations\Group;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;
use Symfony\Component\HttpFoundation\InputBag;

class DriverLifecycleTest extends TestCase
{
  public function test_filterable_resolves_the_configured_default_driver(): void
  {
    config()->set('filterable.default_driver', 'recording');
    config()->set('filterable.drivers.recording', RecordingDriver::class);

    $filterable = Filterable::for(Post::class);

    $this->assertInstanceOf(RecordingDriver::class, $filterable->getDriver());
  }

  public function test_filterable_can_override_the_driver_for_one_instance(): void
  {
    $driver = new RecordingDriver();
    $filterable = Filterable::for(Post::class);

    $result = $filterable->useDriver($driver);

    $this->assertSame($filterable, $result);
    $this->assertSame($driver, $filterable->getDriver());
  }

  public function test_ruleset_dispatches_a_resolved_comparison_to_the_driver(): void
  {
    $this->seedPosts();
    $driver = new RecordingDriver();
    $request = Request::create('/posts', 'GET', [
      'state' => ['eq' => ' ACTIVE '],
    ]);

    $filterable = Filterable::for(Post::class, $request)
      ->using('ruleset')
      ->setAllowedFields(['state'])
      ->setFieldsMap(['state' => 'status'])
      ->setSanitizers(['state' => fn($value) => strtolower(trim($value))])
      ->useDriver($driver);

    $results = $filterable->get();

    $this->assertCount(1, $results);
    $this->assertComparison($driver->operations[0], 'status', '=', 'active');
    $this->assertSame(' ACTIVE ', $filterable->applied('state')->rawValue);
  }

  public function test_expression_dispatches_comparisons_to_the_driver(): void
  {
    $this->seedPosts();
    $driver = new RecordingDriver();
    $request = Request::create('/posts', 'GET', [
      'filter' => ['views' => ['gte' => 20]],
    ]);

    $results = Filterable::for(Post::class, $request)
      ->using('expression')
      ->setAllowedFields(['views'])
      ->useDriver($driver)
      ->get();

    $this->assertSame([20, 30], $results->pluck('views')->sort()->values()->all());
    $this->assertComparison($driver->operations[0], 'views', '>=', 20);
  }

  public function test_tree_dispatches_a_complete_operation_tree_to_the_driver(): void
  {
    $this->seedPosts();
    $driver = new RecordingDriver();
    $request = Request::create('/posts');
    $request->setJson(new InputBag([
      'filter' => [
        'and' => [
          ['field' => 'status', 'operator' => 'eq', 'value' => 'active'],
          ['or' => [
            ['field' => 'views', 'operator' => 'gte', 'value' => 30],
          ]],
        ],
      ],
    ]));

    Filterable::for(Post::class, $request)
      ->using('tree')
      ->setAllowedFields(['status', 'views'])
      ->useDriver($driver)
      ->get();

    $this->assertCount(1, $driver->operations);
    $this->assertInstanceOf(Group::class, $driver->operations[0]);
    $this->assertSame('and', $driver->operations[0]->boolean());

    [$status, $nested] = $driver->operations[0]->operations();

    $this->assertComparison($status, 'status', '=', 'active');
    $this->assertInstanceOf(Group::class, $nested);
    $this->assertSame('or', $nested->boolean());
    $this->assertComparison($nested->operations()[0], 'views', '>=', 30);
  }

  public function test_invokable_domain_methods_keep_their_existing_database_behavior(): void
  {
    $this->seedPosts();
    $driver = new RecordingDriver();
    $request = Request::create('/posts', 'GET', ['status' => 'active']);
    $filter = new class($request) extends Filterable {
      protected $filters = ['status'];

      protected function status(Payload $payload): Builder
      {
        return $this->getBuilder()->where($payload->field, $payload->value);
      }
    };

    $results = $filter
      ->setModel(Post::class)
      ->setBuilder(Post::query())
      ->useDriver($driver)
      ->get();

    $this->assertCount(1, $results);
    $this->assertSame([], $driver->operations);
  }

  public function test_the_eloquent_lifecycle_rejects_a_non_eloquent_driver_result(): void
  {
    $this->seedPosts();
    $request = Request::create('/posts', 'GET', [
      'views' => ['gte' => 20],
    ]);

    $this->expectException(InvalidDriverResultException::class);

    Filterable::for(Post::class, $request)
      ->using('ruleset')
      ->strict()
      ->setAllowedFields(['views'])
      ->useDriver(new InvalidResultDriver())
      ->get();
  }

  public function test_tree_payloads_are_not_committed_when_the_driver_fails(): void
  {
    $this->seedPosts();
    $request = Request::create('/posts');
    $request->setJson(new InputBag([
      'filter' => [
        'and' => [
          ['field' => 'status', 'operator' => 'eq', 'value' => 'active'],
          ['field' => 'views', 'operator' => 'gte', 'value' => 10],
        ],
      ],
    ]));
    $filterable = Filterable::for(Post::class, $request)
      ->using('tree')
      ->setAllowedFields(['status', 'views'])
      ->useDriver(new InvalidResultDriver());

    try {
      $filterable->get();
      $this->fail('Expected the incompatible Driver result to fail.');
    } catch (InvalidDriverResultException) {
      $this->assertSame([], $filterable->applied());
    }
  }

  private function assertComparison(
    Operation $operation,
    string $field,
    string $operator,
    mixed $value
  ): void {
    $this->assertInstanceOf(Comparison::class, $operation);
    $this->assertSame($field, $operation->field());
    $this->assertSame($operator, $operation->operator());
    $this->assertSame($value, $operation->value());
  }

  private function seedPosts(): void
  {
    Post::factory()->create(['status' => 'active', 'views' => 10]);
    Post::factory()->create(['status' => 'pending', 'views' => 20]);
    Post::factory()->create(['status' => 'stopped', 'views' => 30]);
  }
}

class RecordingDriver implements Driver
{
  /**
   * @var list<Operation>
   */
  public array $operations = [];

  public function apply(Operation $operation, object $query): object
  {
    $this->operations[] = $operation;

    return (new DatabaseDriver())->apply($operation, $query);
  }
}

class InvalidResultDriver implements Driver
{
  public function apply(Operation $operation, object $query): object
  {
    return new \stdClass();
  }
}
