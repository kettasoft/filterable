<?php
namespace Kettasoft\Filterable\Commands\Concerns;

use Illuminate\Support\Facades\File;
use Kettasoft\Filterable\Filterable;

trait CommandHelpers
{
    /**
     * Highlight text with a specified color.
     *
     * @param string $highlighter
     * @param string $color
     * @return string
     */
    protected function highlight(string $highlighter, string $color): string
    {
        return sprintf('<fg=%s>%s</fg=%s>', $color, $highlighter, $color);
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

    /**
     * Get the provided data keys from the filter.
     *
     * @param mixed $filter
     * @return array
     */
    protected function getProvidedData($filter): array
    {
        return method_exists($filter, 'provided')
            ? array_keys($filter->provided())
            : [];
    }

    /**
     * Resolve the Filterable class from input.
     *
     * @param string $input
     * @return string|null
     */
    protected function resolveFilterClass(string $input): ?string
    {
        // If fully qualified, just return
        if (class_exists($input)) return $input;

        // Try with App\Filters namespace
        $guessed = "App\\Filters\\{$input}";
        if (class_exists($guessed)) return $guessed;

        // Try with configured namespace
        $namespace = config('filterable.namespace', 'App\\Filters');
        $alt = sprintf('%s\\%s', rtrim($namespace, '\\'), $input);
        if (class_exists($alt)) return $alt;

        return null;
    }
}