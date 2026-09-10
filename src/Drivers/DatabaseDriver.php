<?php

namespace Kettasoft\Filterable\Drivers;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Kettasoft\Filterable\Drivers\Contracts\Driver;
use Kettasoft\Filterable\Exceptions\InvalidDriverTargetException;
use Kettasoft\Filterable\Exceptions\UnsupportedOperationException;
use Kettasoft\Filterable\Operations\Comparison;
use Kettasoft\Filterable\Operations\Contracts\Operation;
use Kettasoft\Filterable\Operations\Group;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Engines\Foundation\Operators\OperatorResolver;

class DatabaseDriver implements Driver
{
    /**
     * The operator resolver instance.
     *
     * @var OperatorResolver
     */
    private OperatorResolver $operators;

    /**
     * Create a new database driver instance.
     * 
     * @param OperatorResolver|null $operators
     */
    public function __construct(?OperatorResolver $operators = null)
    {
        $this->operators = $operators ?? OperatorResolver::fromConfig();
    }

    /**
     * Apply an operation to a query.
     *
     * @param Operation $operation
     * @param object $query
     *
     * @return object
     *
     * @throws InvalidDriverTargetException
     * @throws UnsupportedOperationException
     */
    public function apply(Operation $operation, object $query): object
    {
        if (! $query instanceof Builder) {
            throw new InvalidDriverTargetException('database', $query, Builder::class);
        }

        return $this->applyToBuilder($operation, $query);
    }

    /**
     * Apply an operation to a query builder.
     *
     * @param Operation $operation
     * @param Builder $builder
     *
     * @return Builder
     *
     * @throws UnsupportedOperationException
     */
    private function applyToBuilder(Operation $operation, Builder $builder): Builder
    {
        if ($operation instanceof Comparison) {
            return $this->applyComparison($operation, $builder);
        }

        if ($operation instanceof Group) {
            return $this->applyGroup($operation, $builder);
        }

        throw new UnsupportedOperationException('database', $operation);
    }

    private function applyComparison(Comparison $operation, Builder $builder): Builder
    {
        if (str_contains($operation->field(), '.')) {
            return $this->applyRelationalComparison($operation, $builder);
        }

        return $this->applyOperator($operation, $builder);
    }

    private function applyRelationalComparison(Comparison $operation, Builder $builder): Builder
    {
        $segments = explode('.', $operation->field());
        $field = array_pop($segments);
        $relation = implode('.', $segments);

        return $builder->whereHas($relation, function (Builder $query) use ($operation, $field): Builder {
            return $this->applyOperator(
                new Comparison($field, $operation->operator(), $operation->value()),
                $query
            );
        });
    }

    private function applyGroup(Group $group, Builder $builder): Builder
    {
        if ($group->operations() === []) {
            return $builder;
        }

        return $builder->where(function (Builder $query) use ($group): void {
            foreach ($group->operations() as $index => $operation) {
                $method = $index === 0 || $group->boolean() === 'and' ? 'where' : 'orWhere';

                $query->{$method}(function (Builder $nested) use ($operation): void {
                    $this->applyToBuilder($operation, $nested);
                });
            }
        });
    }

    private function applyOperator(Comparison $operation, Builder $builder): Builder
    {
        $payload = new Payload(
            $operation->field(),
            $operation->operator(),
            $operation->value(),
            $operation->value(),
        );

        return $this->operators->resolve($operation->operator())->apply($builder, $payload);
    }
}
