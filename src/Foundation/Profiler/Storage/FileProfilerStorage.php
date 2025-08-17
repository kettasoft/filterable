<?php

namespace Kettasoft\Filterable\Foundation\Profiler\Storage;

use Kettasoft\Filterable\Foundation\Profiler\Contracts\ProfilerStorageContract;

class FileProfilerStorage implements ProfilerStorageContract
{
  /**
   * Store the profiler data.
   *
   * @param array $data
   * @return void
   */
  public function store(mixed $data): void
  {
    $data = array_merge($data, [
      // Additional data can be added here if needed
    ]);

    try {
      $filePath = storage_path('logs/filterable-profiler.log');
      file_put_contents($filePath, json_encode($data) . PHP_EOL, FILE_APPEND);
    } catch (\Exception $e) {
      // Handle any exceptions that may occur during file writing
      error_log('Profiler storage error: ' . $e->getMessage());
    }
  }
}
