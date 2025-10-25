<?php

namespace Kettasoft\Filterable\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetupFilterableCommand extends Command
{
    protected $signature = 'filterable:setup {--force : Overwrite existing config if present}';
    protected $description = 'Setup Filterable package by publishing its configuration and preparing directories';

    public function handle()
    {
        $this->info('🚀 Setting up Filterable package...');

        // 1. Publish the config
        $this->callSilent('vendor:publish', [
            '--tag' => 'filterable-config',
            '--force' => $this->option('force'),
        ]);

        $this->info('✅ Configuration file published: config/filterable.php');

        // 2. Ensure Filters folder exists
        $filtersPath = app_path('Http/Filters');
        if (!File::exists($filtersPath)) {
            File::makeDirectory($filtersPath);
            $this->info('📁 Created directory: app/Http/Filters');
        } else {
            $this->line('📁 Directory already exists: app/Http/Filters');
        }

        $this->line('');
        $this->comment('🎉 Setup complete! You can now create your first filter with:');
        $this->info('php artisan filterable:make-filter PostFilter --filters=author,title');

        return Command::SUCCESS;
    }
}
