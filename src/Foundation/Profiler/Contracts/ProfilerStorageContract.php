<?php

namespace Kettasoft\Filterable\Foundation\Profiler\Contracts;

/**
 * Interface for Profiler Storage.
 *
 * This interface defines the methods required for storing.
 */
interface ProfilerStorageContract
{
  /**
   * Store the profiler data.
   *
   * @param array $data
   * @return void
   */
  public function store(mixed $data): void;
}
