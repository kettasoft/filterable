<?php
namespace Kettasoft\Filterable\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Kettasoft\Filterable\Filterable;

/**
 * Command to list all registered Filterable classes and their configurations.
 * 
 * This command scans the app/Http/Filters directory for classes that extend
 * the Filterable base class and displays their associated models, allowed fields,
 * allowed operators, and engines in a tabular format.
 * 
 * @package Kettasoft\Filterable\Commands
 */
class ListFiltersCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'filterable:list';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'List all registered Filterable classes and their configurations.';

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $filters = $this->getFilters();

        if (empty($filters)) {
            $this->warn('No filterable classes found.');
            return;
        }

        $rows = [];

        foreach ($filters as $filterClass) {
            $instance = new $filterClass();

            $rows[] = [
                'Filter'   => class_basename($filterClass),
                'Model'    => $this->getModel($instance),
                'Fields'   => implode(', ', $instance->getAllowedFields() ?? []),
                'Operators'=> implode(', ', $instance->getAllowedOperators() ?? []),
                'Engine'   => $this->getEngine($instance),
            ];
        }

        $this->table(['Filter', 'Model', 'Fields', 'Operators', 'Engine'], $rows);
    }

    /**
     * Scan the app/Http/Filters directory for Filterable classes.
     *
     * @return array
     */
    protected function getFilters(): array
    {
        $filtersPath = app_path('Http/Filters');
        if (!File::isDirectory($filtersPath)) return [];

        $classes = [];
        foreach (File::allFiles($filtersPath) as $file) {
            $class = $this->pathToClass($file->getRealPath());
            if (class_exists($class) && is_subclass_of($class, Filterable::class)) {
                $classes[] = $class;
            }
        }
        return $classes;
    }

    /**
     * Convert a file path to a fully qualified class name.
     *
     * @param string $path
     * @return string
     */
    protected function pathToClass($path): string
    {
        $relative = str_replace([app_path() . '/', '.php'], '', $path);
        return 'App\\' . str_replace('/', '\\', $relative);
    }

    /**
     * Get the model associated with the filter.
     *
     * @param mixed $filter
     * @return string
     */
    protected function getModel($filter): string
    {
        return method_exists($filter, 'getModel') ? (class_basename($filter->getModel()) ) : '-';
    }

    /**
     * Get the engine associated with the filter.
     *
     * @param mixed $filter
     * @return string
     */
    protected function getEngine($filter): string
    {
        return method_exists($filter, 'getEngin') ? class_basename($filter->getEngin()) : '-';
    }
}
