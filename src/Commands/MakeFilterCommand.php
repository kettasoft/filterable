<?php

namespace Kettasoft\Filterable\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Kettasoft\Filterable\Support\Stub;

class MakeFilterCommand extends Command
{
  protected $signature = 'filterable:make-filter
                            {name : The filter class name}
                            {--filters= : Comma-separated filter methods (e.g. status,title)}
                            {--namespace= : Override the generated class namespace}
                            {--path= : Override the directory where the filter file will be created}
                            {--force : Overwrite existing filter if it exists}';

  protected $description = 'Create a new Eloquent filter class';

  public function handle()
  {
    $name = trim($this->argument('name'));
    $class = $this->resolveClassName($name);
    $keys = $this->option('filters');
    $savePath = $this->getFilterSavingPath();
    $namespace = $this->getFilterNamespace();
    $filePath = $savePath . "/{$class}.php";

    Stub::setBasePath(config('filterable.generator.stubs'));

    // Ensure directory exists
    if (!File::exists($savePath)) {
      File::makeDirectory($savePath, 0755, true);
    }

    // Prevent overwriting existing files
    if (File::exists($filePath) && !$this->option('force')) {
      $this->error("❌ Filter class '{$class}.php' already exists at {$savePath}.");
      $this->warn('Use the --force option to overwrite it.');
      return Command::FAILURE;
    }

    // If no filters provided → create simple class
    if (!$keys) {
      Stub::create('filter.stub', [
        'CLASS' => $class,
        'FILTER_KEYS' => '',
        'METHODS' => '',
        'NAMESPACE' => $namespace,
      ])->saveTo($savePath, "{$class}.php");

      $this->info("✅ Filter class '{$class}.php' created successfully.");
      return Command::SUCCESS;
    }

    // Split filters correctly
    $keys = str_contains($keys, ',')
      ? array_map('trim', explode(',', Str::camel($keys)))
      : [Str::camel($keys)];

    // Generate methods stubs
    $methods = [];
    foreach ($keys as $key) {
      // Reject invalid names (like containing symbols or starting with number)
      if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
        $this->error("⚠️  Invalid method name: '$key'");
        return Command::FAILURE;
      }

      $methods[] = Stub::create('method.stub', ['NAME' => $key])->render();
    }

    // Create final filter class
    Stub::create('filter.stub', [
      'CLASS' => $class,
      'METHODS' => implode("\n\n", $methods),
      'FILTER_KEYS' => "'" . implode("','", $keys) . "'",
      'NAMESPACE' => $namespace,
    ])->saveTo($savePath, "{$class}.php");

    $this->info("✅ Filter '{$class}.php' created successfully with methods: " . implode(', ', $keys));
    return Command::SUCCESS;
  }

  /**
   * Get the filter saving path.
   *
   * @return string
   */
  protected function getFilterSavingPath(): string
  {
    $path = trim((string) $this->option('path'));

    if ($path === '') {
      return rtrim((string) config('filterable.save_filters_at', app_path('Http/Filters')), '/\\');
    }

    if ($this->isAbsolutePath($path)) {
      return rtrim($path, '/\\');
    }

    return rtrim(base_path($path), '/\\');
  }

  /**
   * Get the filter namespace.
   *
   * @return string
   */
  protected function getFilterNamespace(): string
  {
    $namespace = trim((string) $this->option('namespace'));

    if ($namespace === '') {
      $namespace = (string) config('filterable.namespace', config('filterable.filter_namespace', 'App\\Http\\Filters'));
    }

    return trim(str_replace('/', '\\', $namespace), '\\');
  }

  /**
   * Resolve the class name from the given name.
   *
   * @param string $name
   * @return string
   */
  protected function resolveClassName(string $name): string
  {
    return Str::of($name)->replace('/', '\\')->afterLast('\\')->toString();
  }

  /**
   * Check if the given path is an absolute path.
   *
   * @param string $path
   * @return bool
   */
  protected function isAbsolutePath(string $path): bool
  {
    return Str::startsWith($path, ['/']) || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path) === 1;
  }
}
