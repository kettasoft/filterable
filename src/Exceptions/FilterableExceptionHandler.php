<?php

namespace Kettasoft\Filterable\Exceptions;

use Kettasoft\Filterable\Engines\Exceptions\SkipExecution;
use Kettasoft\Filterable\Engines\Foundation\Engine;
use Kettasoft\Filterable\Exceptions\Contracts\ExceptionHandlerInterface;

abstract class FilterableExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @inheritDoc
     */
    abstract public function handle(\Throwable $exception, Engine $engine): bool;

    /**
     * Check if the strict mode is enable in config.
     * @return bool
     */
    protected function isStrictThrowing(): bool
    {
        return config('filterable.exception.strict', false);
    }

    /**
     * Check if the exception is related to skipping filters.
     * @param \Throwable $exception
     * @return bool
     */
    protected function hasSkipping($exception): bool
    {
        return $exception instanceof SkipExecution;
    }

    /**
     * Check if the exception is related to strictness.
     * @param \Throwable $exception
     * @return bool
     */
    protected function isStrictness($exception): bool
    {
        if ($exception instanceof StrictnessException) {
            return true;
        }

        return false;
    }
}
