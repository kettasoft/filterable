<?php
namespace Kettasoft\Filterable\Commands;

use Illuminate\Console\Command;
use Kettasoft\Filterable\Commands\Concerns\CommandHelpers;

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
    use CommandHelpers;

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
}
