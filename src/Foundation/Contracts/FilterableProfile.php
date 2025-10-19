<?php

namespace Kettasoft\Filterable\Foundation\Contracts;

use Kettasoft\Filterable\Filterable;

interface FilterableProfile
{
    /**
     * Handle the given filterable context.
     * @param Filterable $context
     * @return Filterable
     */
    public function __invoke(Filterable $context): Filterable;
}
