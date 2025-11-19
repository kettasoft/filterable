<?php

namespace Kettasoft\Filterable\Exceptions\Contracts;

use Kettasoft\Filterable\Engines\Foundation\Engine;

interface ExceptionHandlerInterface
{
    /**
     * Handle an exception that occurs during filtering.
     *
     * @param \Throwable $exception The exception that was thrown.
     * @param \Kettasoft\Filterable\Engines\Foundation\Engine $engine The engine in which the exception occurred.
     * @throws \Throwable
     * @return bool
     */
    public function handle(\Throwable $exception, Engine $engine): bool;
}
