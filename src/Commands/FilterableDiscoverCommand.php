<?php

namespace Kettasoft\Filterable\Commands;

use ReflectionClass;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

/**
 * Discover searchable columns and suggest indexes for a model.
 * 
 * Usage: php artisan filterable:discover Post --suggest-indexes
 */
class FilterableDiscoverCommand extends Command
{
    protected $signature = 'filterable:discover
                          {model : Model class name}
                          {--suggest-indexes : Suggest indexes for searchable columns}
                          {--create-indexes : Create suggested indexes}
                          {--analyze-data : Analyze actual data for suggestions}
                          {--connection= : Database connection to use}';

    protected $description = 'Discover searchable columns.';

    protected Model|Builder $model;
    protected string $table;
    protected array $columns = [];
    protected array $relationships = [];
    protected array $suggestedIndexes = [];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Discovering filterable columns...');

        // Resolve model
        if (!$this->resolveModel()) {
            return self::FAILURE;
        }

        $this->newLine();
        $this->info("Model: " . get_class($this->model));
        $this->info("Table: {$this->table}");

        // Discover columns
        $this->discoverColumns();

        // Analyze relationships
        $this->discoverRelationships();

        // Display results
        $this->displayDiscovery();

        // Suggest indexes
        if ($this->option('suggest-indexes')) {
            $this->newLine();
            $this->suggestIndexes();
        }

        // Create indexes if requested
        if ($this->option('create-indexes')) {
            $this->newLine();
            $this->createIndexes();
        }

        $this->newLine();
        $this->info('✅ Discovery completed!');

        return self::SUCCESS;
    }

    protected function resolveModel(): bool
    {
        $modelClass = $this->argument('model');

        $possibleClasses = [
            $modelClass,
            "App\\Models\\{$modelClass}",
        ];

        foreach ($possibleClasses as $class) {
            if (class_exists($class)) {
                try {
                    $this->model = new $class();

                    if (!$this->model instanceof Model) {
                        $this->error("Class {$class} is not an Eloquent model");
                        return false;
                    }

                    // Set connection if specified
                    if ($connection = $this->option('connection')) {
                        $this->model = $this->model->on($connection);
                    }

                    $this->table = $this->model->getTable();
                    return true;
                } catch (\Exception $e) {
                    $this->error("Error instantiating model: " . $e->getMessage());
                    return false;
                }
            }
        }

        $this->error("Model class not found: {$modelClass}");
        return false;
    }

    protected function discoverColumns(): void
    {
        $connection = $this->model->getConnection();
        $connectionName = $connection->getName();

        // Get columns using Schema facade
        $columns = Schema::connection($connectionName)->getColumnListing($this->table);

        foreach ($columns as $column) {
            // Get column type using raw query for better compatibility
            $columnInfo = $this->getColumnInfo($column, $connectionName);

            $type = $columnInfo['type'] ?? 'unknown';

            $columnData = [
                'name' => $column,
                'type' => $type,
                'searchable' => $this->isSearchableColumn($column, $type),
                'filterable' => $this->isFilterableColumn($column, $type),
                'sortable' => true,
                'indexed' => $this->isColumnIndexed($column),
                'suggestion' => $this->getColumnSuggestion($column, $type),
            ];

            // Analyze data if requested
            if ($this->option('analyze-data')) {
                $columnData['stats'] = $this->analyzeColumnData($column, $type);
            }

            $this->columns[$column] = $columnData;
        }
    }

    protected function getColumnInfo(string $column, string $connectionName): array
    {
        try {
            // For MySQL/MariaDB
            $result = DB::connection($connectionName)
                ->select("SHOW COLUMNS FROM {$this->table} WHERE Field = ?", [$column]);

            if (!empty($result)) {
                $columnDetails = (array) $result[0];
                $type = $columnDetails['Type'] ?? 'unknown';

                // Parse type (e.g., "varchar(255)" -> "varchar")
                if (preg_match('/^(\w+)/', $type, $matches)) {
                    $type = $matches[1];
                }

                return [
                    'type' => $type,
                    'nullable' => ($columnDetails['Null'] ?? 'YES') === 'YES',
                    'default' => $columnDetails['Default'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            // Fallback to Schema facade
            try {
                $type = Schema::connection($connectionName)->getColumnType($this->table, $column);
                return ['type' => $type];
            } catch (\Exception $e) {
                // Ignore
            }
        }

        return ['type' => 'unknown'];
    }

    protected function isSearchableColumn(string $column, string $type): bool
    {
        // Text-based columns are searchable
        $searchableTypes = ['string', 'text', 'varchar', 'char', 'longtext', 'mediumtext'];

        if (in_array(strtolower($type), $searchableTypes)) {
            return true;
        }

        // Common searchable column names
        $searchableNames = ['name', 'title', 'description', 'content', 'email', 'username', 'slug'];

        foreach ($searchableNames as $name) {
            if (str_contains(strtolower($column), $name)) {
                return true;
            }
        }

        return false;
    }

    protected function isFilterableColumn(string $column, string $type): bool
    {
        // Foreign keys
        if (str_ends_with($column, '_id')) {
            return true;
        }

        // Status/enum columns
        $filterableNames = ['status', 'type', 'category', 'role', 'state'];

        foreach ($filterableNames as $name) {
            if (str_contains(strtolower($column), $name)) {
                return true;
            }
        }

        // Boolean columns
        if (in_array(strtolower($type), ['boolean', 'bool', 'tinyint'])) {
            return true;
        }

        return false;
    }

    protected function isColumnIndexed(string $column): bool
    {
        $connection = $this->model->getConnection();
        $databaseName = $connection->getDatabaseName();

        try {
            // Use SHOW INDEX query instead of Doctrine
            $indexes = DB::connection($connection->getName())
                ->select("SHOW INDEX FROM {$this->table} WHERE Column_name = ?", [$column]);

            return !empty($indexes);
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function getColumnSuggestion(string $column, string $type): string
    {
        if ($this->isSearchableColumn($column, $type)) {
            $indexed = $this->isColumnIndexed($column);

            if (!$indexed && $this->shouldHaveIndex($column, $type)) {
                return 'searchable (needs index)';
            }

            return $indexed ? 'searchable (indexed)' : 'searchable';
        }

        if ($this->isFilterableColumn($column, $type)) {
            return $this->isColumnIndexed($column) ? 'filterable (indexed)' : 'filterable (needs index)';
        }

        return 'sortable only';
    }

    protected function shouldHaveIndex(string $column, string $type): bool
    {
        // Foreign keys should always have indexes
        if (str_ends_with($column, '_id')) {
            return true;
        }

        // Commonly searched columns
        $highPriorityColumns = ['email', 'username', 'slug', 'status'];

        return in_array(strtolower($column), $highPriorityColumns);
    }

    protected function analyzeColumnData(string $column, string $type): array
    {
        try {
            $stats = [
                'distinct_count' => DB::table($this->table)->distinct()->count($column),
                'null_count' => DB::table($this->table)->whereNull($column)->count(),
                'sample_values' => DB::table($this->table)
                    ->select($column)
                    ->distinct()
                    ->limit(5)
                    ->pluck($column)
                    ->toArray(),
            ];

            // For text columns, get average length
            if ($this->isSearchableColumn($column, $type)) {
                $stats['avg_length'] = DB::table($this->table)
                    ->selectRaw("AVG(LENGTH({$column})) as avg_len")
                    ->value('avg_len');
            }

            return $stats;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    protected function discoverRelationships(): void
    {
        $reflection = new ReflectionClass($this->model);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            // Only check methods defined in the model class itself
            if ($method->class !== get_class($this->model)) {
                continue;
            }

            $name = $method->getName();

            // Skip constructors, magic methods, getters, setters, and scopes
            if (
                $name === '__construct' ||
                str_starts_with($name, '__') ||
                str_starts_with($name, 'get') ||
                str_starts_with($name, 'set') ||
                str_starts_with($name, 'scope')
            ) {
                continue;
            }

            // Skip methods that require parameters
            if ($method->getNumberOfRequiredParameters() > 0) {
                continue;
            }

            // Skip known non-relation methods
            $skipMethods = [
                'notify',
                'notifyNow',
                'routeNotificationFor',
                'toArray',
                'toJson',
                'jsonSerialize',
                'fresh',
                'refresh',
                'replicate',
                'is',
                'isNot',
                'getKey',
                'getTable',
                'getConnection',
                'newQuery',
                'save',
                'delete',
                'restore',
                'forceDelete',
            ];

            if (in_array($name, $skipMethods)) {
                continue;
            }

            try {
                $return = $method->invoke($this->model);

                if ($return instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
                    $this->relationships[$name] = [
                        'type' => class_basename($return),
                        'related' => get_class($return->getRelated()),
                        'searchable' => $this->isRelationSearchable($return),
                    ];
                }
            } catch (\Exception $e) {
                // Ignore methods that throw errors or require specific context
                continue;
            }
        }
    }

    protected function isRelationSearchable($relation): bool
    {
        $related = $relation->getRelated();
        $relatedTable = $related->getTable();

        try {
            $columns = Schema::getColumnListing($relatedTable);

            // Check for name/title columns
            return collect($columns)->contains(
                fn($col) =>
                in_array($col, ['name', 'title', 'email', 'username'])
            );
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function displayDiscovery(): void
    {
        $this->newLine();
        $this->info('📋 Discovered Columns:');
        $this->newLine();

        $searchable = collect($this->columns)->filter(fn($col) => $col['searchable']);
        $filterable = collect($this->columns)->filter(fn($col) => $col['filterable']);

        if ($searchable->isNotEmpty()) {
            $this->line('<fg=green>Searchable Columns:</>');
            $rows = $searchable->map(function ($col, $name) {
                return [
                    $name,
                    $col['type'],
                    $col['indexed'] ? '✅' : '❌',
                    $col['suggestion'],
                ];
            })->values()->toArray();

            $this->table(['Column', 'Type', 'Indexed', 'Suggestion'], $rows);
        }

        $this->newLine();

        if ($filterable->isNotEmpty()) {
            $this->line('<fg=blue>Filterable Columns:</>');
            $rows = $filterable->map(function ($col, $name) {
                return [
                    $name,
                    $col['type'],
                    $col['indexed'] ? '✅' : '❌',
                ];
            })->values()->toArray();

            $this->table(['Column', 'Type', 'Indexed'], $rows);
        }

        if (!empty($this->relationships)) {
            $this->newLine();
            $this->line('<fg=yellow>Relationships:</>');
            $rows = collect($this->relationships)->map(function ($rel, $name) {
                return [
                    $name,
                    $rel['type'],
                    class_basename($rel['related']),
                    $rel['searchable'] ? '✅' : '❌',
                ];
            })->values()->toArray();

            $this->table(['Relation', 'Type', 'Model', 'Searchable'], $rows);
        }

        // Display data analysis if available
        if ($this->option('analyze-data')) {
            $this->displayDataAnalysis();
        }
    }

    protected function displayDataAnalysis(): void
    {
        $this->newLine();
        $this->info('📊 Data Analysis:');
        $this->newLine();

        foreach ($this->columns as $name => $col) {
            if (isset($col['stats']) && !isset($col['stats']['error'])) {
                $stats = $col['stats'];

                $this->line("<fg=cyan>{$name}:</>");
                $this->line("  Distinct values: {$stats['distinct_count']}");
                $this->line("  Null count: {$stats['null_count']}");

                if (isset($stats['avg_length'])) {
                    $this->line("  Avg length: " . number_format($stats['avg_length'], 2));
                }

                if (!empty($stats['sample_values'])) {
                    $samples = implode(', ', array_slice($stats['sample_values'], 0, 3));
                    $this->line("  Samples: {$samples}");
                }

                $this->newLine();
            }
        }
    }

    protected function suggestIndexes(): void
    {
        $this->info('💡 Index Suggestions:');
        $this->newLine();

        foreach ($this->columns as $name => $col) {
            if (!$col['indexed'] && $this->shouldHaveIndex($name, $col['type'])) {
                $reason = $this->getIndexReason($name, $col);

                $this->suggestedIndexes[] = [
                    'column' => $name,
                    'type' => $this->suggestIndexType($name, $col),
                    'reason' => $reason,
                ];
            }
        }

        if (empty($this->suggestedIndexes)) {
            $this->info('No index suggestions - all important columns are indexed');
            return;
        }

        $rows = collect($this->suggestedIndexes)->map(fn($idx) => [
            $idx['column'],
            $idx['type'],
            $idx['reason'],
        ])->toArray();

        $this->table(['Column', 'Index Type', 'Reason'], $rows);
    }

    protected function getIndexReason(string $column, array $columnInfo): string
    {
        if (str_ends_with($column, '_id')) {
            return 'Foreign key - frequently used in joins';
        }

        if ($columnInfo['searchable']) {
            return 'Searchable column - will improve search performance';
        }

        if ($columnInfo['filterable']) {
            return 'Filterable column - frequently used in WHERE clauses';
        }

        return 'General performance improvement';
    }

    protected function suggestIndexType(string $column, array $columnInfo): string
    {
        if ($columnInfo['searchable'] && $columnInfo['type'] === 'text') {
            return 'FULLTEXT';
        }

        return 'INDEX';
    }

    protected function createIndexes(): void
    {
        if (empty($this->suggestedIndexes)) {
            $this->suggestIndexes();
        }

        if (empty($this->suggestedIndexes)) {
            return;
        }

        if (!$this->confirm('Create ' . count($this->suggestedIndexes) . ' suggested index(es)?')) {
            return;
        }

        $this->info('Creating indexes...');

        foreach ($this->suggestedIndexes as $index) {
            try {
                $indexName = "{$this->table}_{$index['column']}_index";
                $connection = $this->model->getConnection()->getName();

                if ($index['type'] === 'FULLTEXT') {
                    DB::connection($connection)->statement(
                        "ALTER TABLE {$this->table} ADD FULLTEXT INDEX {$indexName} ({$index['column']})"
                    );
                } else {
                    Schema::connection($connection)->table($this->table, function ($table) use ($index) {
                        $table->index($index['column']);
                    });
                }

                $this->info("✅ Created {$index['type']} index on {$index['column']}");
            } catch (\Exception $e) {
                $this->error("❌ Failed to create index on {$index['column']}: " . $e->getMessage());
            }
        }
    }
}
