<?php

namespace Kettasoft\Filterable\Foundation\Profiler\Storage;

use Illuminate\Support\Facades\DB;
use Kettasoft\Filterable\Foundation\Profiler\Contracts\ProfilerStorageContract;

class DatabaseProfilerStorage implements ProfilerStorageContract
{
  /**
   * Store the profiler data.
   *
   * @param array $data
   * @return void
   */
  public function store(mixed $data): void
  {
    DB::table('users')->insert($data);
  }
}
