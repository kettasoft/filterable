<?php

namespace Kettasoft\Filterable\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Kettasoft\Filterable\Support\Stub;

class MakeFilterCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'make:filter {name} {--filters}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Create a new Eloquent filter class';

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $name = $this->argument('name');
    $keys = $this->option('filters');

    Stub::setBasePath(config('filterable.generator.stubs'));

    if (!$keys) {
      Stub::create('filter.stub', [
        'CLASS' => $name,
        'FILTER_KEYS' => '',
        'METHODS' => '',
        'NAMESPACE' => Config::get('filterable.filter_namespace', 'App\\Http\\Filters')
      ])->saveTo($this->getFilterSavingPath(), "{$name}.php");

      $this->info("Filter class '$name'.php created successfully.");
      return 1;
    }

    $keys = str_contains($keys, ',') ?
      explode(strtolower($keys), ',') : [$keys];

    $methods = [];

    foreach ($keys as $key) {
      $methods[] = Stub::create('method.stub', [
        'NAME' => trim($key),
      ])->render();
    }

    Stub::create('filter.stub', [
      'CLASS' => $name,
      'METHODS' => implode("\n\n", $methods),
      'FILTER_KEYS' => "'" . implode("','", $keys) . "'",
      'NAMESPACE' => Config::get('filterable.filter_namespace', 'App\\Http\\Filters')
    ])->saveTo($this->getFilterSavingPath(), "{$name}.php");

    return 1;
  }

  /**
   * Get path of saving new filter classes.
   * @return string
   */
  protected function getFilterSavingPath(): string
  {
    return config('filterable.save_filters_at');
  }
}
