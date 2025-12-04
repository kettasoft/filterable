<?php

namespace Kettasoft\Filterable\Support;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;

class ValidateTableColumns
{
  /**
   * Check if column name is exist in specific table.
   * @param \Illuminate\Database\Eloquent\Model|\Illuminate\Contracts\Database\Eloquent\Builder $instance
   * @param string $column
   * @return bool
   */
  public static function validate(Model|Builder $instance, string $column): bool
  {
    $table = $instance instanceof Model ? $instance->getTable() : $instance->getModel()->getTable();
    return in_array($column, Schema::getColumnListing($table));
  }
}
