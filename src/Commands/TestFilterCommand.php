<?php

namespace Kettasoft\Filterable\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class TestFilterCommand extends Command
{
    protected $signature = 'filterable:test 
        {filter : Filter class name (e.g. UserFilter)} 
        {--model= : The model class to apply filter on (e.g. User)} 
        {--data= : Filter data, e.g. "status=active,age=25"}
        {--explain : Show the query with bindings}';

    protected $description = 'Test a Filterable filter class and show the resulting query.';

    public function handle()
    {
        $filterName = $this->argument('filter');
        $modelName = $this->option('model');
        $dataOption = $this->option('data');
        $explainOption = $this->option('explain');

        $filterNamespace = Config::get('filterable.filter_namespace', 'App\\Http\\Filters');
        $filterClass = Str::contains($filterName, '\\')
            ? $filterName
            : $filterNamespace . '\\' . $filterName;

        if (!class_exists($filterClass)) {
            $this->error("Filter class [$filterClass] not found.");
            return Command::FAILURE;
        }

        $this->line("🔍 Testing filter: <info>{$filterClass}</info>");

        // Resolve model
        if (!$modelName) {
            $this->warn("⚠️  No model provided. Use --model=User");
            return Command::FAILURE;
        }

        $modelClass = Str::contains($modelName, '\\')
            ? $modelName
            : 'App\\Models\\' . $modelName;

        if (!class_exists($modelClass)) {
            $this->error("Model class [$modelClass] not found.");
            return Command::FAILURE;
        }

        $this->line("🧩 Model: <info>{$modelClass}</info>");

        // Parse data
        $data = [];
        if ($dataOption) {
            $pairs = explode(',', $dataOption);
            foreach ($pairs as $pair) {
                [$key, $value] = array_pad(explode('=', $pair, 2), 2, null);
                if ($key) $data[trim($key)] = trim($value);
            }
        }

        if (empty($data)) {
            $this->warn("⚠️  No data provided. Use --data=\"status=active,age=30\"");
            return Command::FAILURE;
        }

        $this->line("Applied filters:");
        foreach ($data as $key => $value) {
            $this->line("  • <comment>{$key}</comment> = {$value}");
        }

        // Apply filter
        try {
            $filter = App::make($filterClass, ['request', request()->merge($data)]);
            $filter->setModel($modelClass);

            $filter = $filter->apply();

            $this->newLine();
            $this->info("✅ Query:");
            $this->info($explainOption ? $filter->toRawSql() : $filter->toSql());
        } catch (\Throwable $e) {
            $this->error("❌ Error applying filter: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
