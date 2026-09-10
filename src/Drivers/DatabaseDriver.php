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
     * @param OperatorResolver|null $operators Resolver used to translate
     *     comparison operators into Eloquent query operations. When omitted,
     *     the resolver is built from the package configuration.
     */
    public function __construct(?OperatorResolver $operators = null)
    {
        $this->operators = $operators ?? OperatorResolver::fromConfig();
    }

    /**
     * Apply a backend-independent operation to an Eloquent query.
     *
     * @param Operation $operation Instruction to translate into Eloquent calls.
     * @param object $query Query object expected to implement Eloquent's Builder contract.
     * @return object The filtered Eloquent builder.
     *
     * @throws InvalidDriverTargetException When the query is not an Eloquent builder.
     * @throws UnsupportedOperationException When the operation type is not supported.
     */
    public function apply(Operation $operation, object $query): object
    {
        if (! $query instanceof Builder) {
            throw new InvalidDriverTargetException('database', $query, Builder::class);
        }

        return $this->applyToBuilder($operation, $query);
    }

    /**
     * Dispatch an operation to its database-specific application method.
     *
     * @param Operation $operation Instruction being applied.
     * @param Builder $builder Eloquent builder receiving the instruction.
     * @return Builder The updated Eloquent builder.
     *
     * @throws UnsupportedOperationException When no database implementation exists.
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

    /**
     * Apply a direct or relational comparison to an Eloquent builder.
     *
     * @param Comparison $operation Comparison to translate.
     * @param Builder $builder Eloquent builder receiving the comparison.
     * @return Builder The updated Eloquent builder.
     */
    private function applyComparison(Comparison $operation, Builder $builder): Builder
    {
        if (str_contains($operation->field(), '.')) {
            return $this->applyRelationalComparison($operation, $builder);
        }

        return $this->applyOperator($operation, $builder);
    }

    /**
     * Apply a dotted field comparison through an Eloquent relationship path.
     *
     * The last path segment is treated as the related field and all preceding
     * segments are passed to Eloquent as the relationship path.
     *
     * @param Comparison $operation Relational comparison to translate.
     * @param Builder $builder Parent Eloquent builder.
     * @return Builder The updated parent builder.
     */
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

    /**
     * Apply a logically grouped collection of operations.
     *
     * Nested groups are recursively translated while preserving their AND/OR
     * boundaries inside Eloquent constraint closures.
     *
     * @param Group $group Logical group to translate.
     * @param Builder $builder Eloquent builder receiving the group.
     * @return Builder The updated Eloquent builder.
     */
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

    /**
     * Resolve and execute the operator strategy for a comparison.
     *
     * @param Comparison $operation Comparison containing the resolved operator.
     * @param Builder $builder Eloquent builder receiving the operator.
     * @return Builder The updated Eloquent builder.
     */
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
