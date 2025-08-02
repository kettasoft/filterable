<?php

namespace Kettasoft\Filterable\Foundation\Contracts;

use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;

/**
 * @mixin \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
 */
interface QueryBuilderInterface extends EloquentBuilder, QueryBuilder {}
