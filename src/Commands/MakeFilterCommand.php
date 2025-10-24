<?php

namespace Kettasoft\Filterable\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Kettasoft\Filterable\Support\Stub;

class MakeFilterCommand extends Command
{
  protected $signature = 'filterable:make-filter 
                            {name : The filter class name} 
                            {--filters= : Comma-separated filter methods (e.g. status,title)}
                            {--force : Overwrite existing filter if it exists}';

  protected $description = 'Create a new Eloquent filter class';

  public function handle()
  {
    $name = trim($this->argument('name'));
    $keys = $this->option('filters');

    Stub::setBasePath(config('filterable.generator.stubs'));

    // Ensure directory exists
    $savePath = $this->getFilterSavingPath();
    if (!File::exists($savePath)) {
      File::makeDirectory($savePath, 0755, true);
    }

    // Prevent overwriting existing files
    if (File::exists($savePath . "/{$name}.php") && !$this->option('force')) {
      $this->error("❌ Filter class '{$name}.php' already exists at {$savePath}.");
      $this->warn('Use the --force option to overwrite it.');
      return Command::FAILURE;
    }

    // If no filters provided → create simple class
    if (!$keys) {
      Stub::create('filter.stub', [
        'CLASS' => $name,
        'FILTER_KEYS' => '',
        'METHODS' => '',
        'NAMESPACE' => Config::get('filterable.filter_namespace', 'App\\Http\\Filters')
      ])->saveTo($savePath, "{$name}.php");

      $this->info("✅ Filter class '{$name}.php' created successfully.");
      return Command::SUCCESS;
    }

    // Split filters correctly
    $keys = str_contains($keys, ',')
      ? array_map('trim', explode(',', strtolower($keys)))
      : [strtolower($keys)];

    // Generate methods stubs
    $methods = [];
    foreach ($keys as $key) {
      $methods[] = Stub::create('method.stub', ['NAME' => $key])->render();
    }

    // Create final filter class
    Stub::create('filter.stub', [
      'CLASS' => $name,
      'METHODS' => implode("\n\n", $methods),
      'FILTER_KEYS' => "'" . implode("','", $keys) . "'",
      'NAMESPACE' => Config::get('filterable.filter_namespace', 'App\\Http\\Filters')
    ])->saveTo($savePath, "{$name}.php");

    $this->info("✅ Filter '{$name}.php' created successfully with methods: " . implode(', ', $keys));
    return Command::SUCCESS;
  }

  protected function getFilterSavingPath(): string
  {
    return config('filterable.save_filters_at', app_path('Http/Filters'));
  }
}
