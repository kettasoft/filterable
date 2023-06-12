<?php

namespace Kettasoft\Filterable;

use Kettasoft\Filterable\Contracts\ForceApply;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;

/**
 * Class Filter
 * @package Kettasoft\Filterable
 */
class Filter
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $filters;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * Filter constructor.
     * @param  Builder|Model|string $builder
     * @param  array  $data
     * @param  array  $filters
     */
    public function __construct($builder, array $data, array $filters)
    {
        $this->data = $data;
        $this->filters = $this->parseFilters($filters);
        $this->builder = $this->resolveBuilder($builder);
    }

    /**
     * @return Builder
     */
    public function filter(): Builder
    {
        foreach ($this->filters as $filter) {
            foreach ($filter['names'] as $name) {
                $this->applyFilter($filter['class'], $name, $filter['parameters']);
            }
        }

        return $this->builder;
    }

    /**
     * @param  string  $filter
     * @param  string  $name
     * @param  array  $parameters
     */
    protected function applyFilter(string $filter, string $name, array $parameters): void
    {
        $filterInstance = $this->getContainer()->make($filter, $this->resolveFilterConstructParameters($filter, $parameters));

        if (! $this->shouldApply($filterInstance, $name)) {
            return;
        }

        if ($this->shouldSanitize($filterInstance)) {
            $this->data[$name] = $filterInstance->sanitize($this->data[$name] ?? null);
        }

        if ($this->shouldValidate($filterInstance) && ! $this->validate($filterInstance, $name)) {
            return;
        }

        $filterInstance->handle($this->data[$name] ?? null, $this->builder, ...$parameters);
    }

    /**
     * @param  string  $filter
     * @param  array  $parameters
     * @return array
     */
    protected function resolveFilterConstructParameters(string $filter, array $parameters): array
    {
        $resolvedParams = [];

        try {
            $reflector = new ReflectionClass($filter);
            if (is_null($constructor = $reflector->getConstructor())) {
                return $resolvedParams;
            }
            foreach ($constructor->getParameters() as $parameter) {
                if ($parameter->isVariadic()) {
                    continue;
                }

                if (! $parameter->hasType() || in_array($parameter->getType()->getName(), ['int', 'string', 'float'])) {
                    $resolvedParams[$parameter->getName()] = $parameters[count($resolvedParams)] ?? null;
                }
            }
        } catch (ReflectionException $e) {
        }

        return $resolvedParams;
    }

    /**
     * @param  object  $filter
     * @param  string  $name
     * @return bool
     */
    protected function shouldApply(object $filter, string $name): bool
    {
        return $filter instanceof ForceApply || isset($this->data[$name]);
    }

    /**
     * @param  object  $filter
     * @return bool
     */
    protected function shouldValidate(object $filter): bool
    {
        return method_exists($filter, 'validate');
    }

    /**
     * @param  object  $filter
     * @return bool
     */
    protected function shouldSanitize(object $filter): bool
    {
        return method_exists($filter, 'sanitize');
    }

    /**
     * @param  object  $filter
     * @param string $name
     * @return bool
     */
    protected function validate(object $filter, string $name): bool
    {
        $validation = $filter->validate(
            $this->data[$name] ?? null
        );
        if (is_bool($validation)) {
            return $validation;
        }

        if (is_string($validation) || is_array($validation)) {
            $rules = is_array($validation) && $this->hasKeyStartsWith($validation, 'value') ? $validation : ['value' => $validation];

            return ! Validator::make([
                'value' => $this->data[$name] ?? null,
            ], $rules)->fails();
        }

        return false;
    }

    /**
     * @param  array  $array
     * @param  string  $key
     * @return bool
     */
    protected function hasKeyStartsWith(array $array, string $key): bool
    {
        foreach (array_keys($array) as $array_key) {
            if (Str::startsWith($array_key, $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array  $filters
     * @return array
     */
    protected function parseFilters(array $filters): array
    {
        $parsedFilters = [];
        foreach ($filters as $key => $value) {
            if (is_string($key)) {
                [$class, $parameters] = $this->parseFilterString($key);
                $names = Arr::wrap($value);
            } else {
                [$class, $parameters] = $this->parseFilterString($value);
                $names = Arr::wrap($this->resolveFilterName($class));
            }
            $parsedFilters[] = [
                'names' => $names,
                'class' => $class,
                'parameters' => $parameters,
            ];
        }

        return $parsedFilters;
    }

    /**
     * @param  string  $filter
     * @return string|array
     */
    protected function resolveFilterName(string $filter)
    {
        if (! property_exists($filter, 'name')) {
            return Str::snake(
                Str::replaceLast('Filter', '', class_basename($filter))
            );
        }

        return $filter::$name;
    }

    /**
     * @param $builder
     * @return Builder
     */
    protected function resolveBuilder($builder): Builder
    {
        if ($builder instanceof Builder) {
            return $builder;
        }

        return $builder::query();
    }

    /**
     * Parse full filter string to get name and parameters.
     *
     * @param  string  $filter
     * @return array
     */
    protected function parseFilterString(string $filter): array
    {
        [$class, $parameters] = array_pad(explode(':', $filter, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return [$class, $parameters];
    }

    /**
     * @return Container
     */
    protected function getContainer(): Container
    {
        return Container::getInstance();
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}