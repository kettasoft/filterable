<?php

namespace Kettasoft\Filterable\Exceptions\Handlers;

use Kettasoft\Filterable\Engines\Foundation\Engine;
use Kettasoft\Filterable\Engines\Exceptions\SkipExecution;
use Kettasoft\Filterable\Exceptions\FilterableExceptionHandler;

/**
 * Default exception handler for Filterable.
 */
class DefaultHandler extends FilterableExceptionHandler
{
    /**
     * @inheritDoc
     * @var SkipExecution $exception
     */
    public function handle(\Throwable|SkipExecution $exception, Engine $engine): bool
    {
        if ($this->hasSkipping($exception)) {
            /** @var SkipExecution $exception */

            if ($engine->isStrict() || $this->isStrictThrowing()) {
                throw $exception;
            }

            return false;
        }

        if ($this->isStrictness($exception) || $this->isStrictThrowing()) {
            throw $exception;
        }

        return false;
    }
}
