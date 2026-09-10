<?php

namespace Kettasoft\Filterable\Exceptions\Contracts;

use Throwable;

/**
 * Marks driver infrastructure errors that must never be silently skipped.
 */
interface DriverException extends Throwable
{
}
