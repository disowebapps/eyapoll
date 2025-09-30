<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AnalyzeMigrations extends Command
{
    protected $signature = 'db:analyze';
    protected $description = 'Analyze migrations and seeders for conflicts and unused files';

    public function handle()
    {
        $this->info('🔍 Analyzing Database Structure...');
        
        $this->analyzeMigrations();
        $this->analyzeSeeders();
        
        return 0;
    }

    private function analyzeMigrations()
    {
        $migrationPath = database_path('migrations');
        $files = collect(File::files($migrationPath))->map(fn($file) => $file->getFilename());
        
        // Get ran migrations
        $ranMigrations = collect(DB::table('migrations')->pluck('migration'));
        
        // Find unused migrations
        $unused = $files->filter(function($file) use ($ranMigrations) {
            $migration = str_replace('.php', '', $file);
            return !$ranMigrations->contains($migration);
        });

        // Find table conflicts
        $conflicts = $this->findTableConflicts($files);
        
        // Find duplicate table creations
        $duplicates = $this->findDuplicateTableCreations($files);

        $this->displayMigrationResults($unused, $conflicts, $duplicates, $ranMigrations->count(), $files->count());
    }

    private function findTableConflicts($files)
    {
        $conflicts = [];
        $tables = [];
        
        foreach ($files as $file) {
            $content = File::get(database_path("migrations/{$file}"));
            
            // Extract table operations
            preg_match_all('/Schema::(create|table|drop)\([\'"]([^\'"]+)/', $content, $matches);
            
            foreach ($matches[2] as $i => $table) {
                $operation = $matches[1][$i];
                
                if (!isset($tables[$table])) {
                    $tables[$table] = [];
                }
                
                $tables[$table][] = [
                    'file' => $file,
                    'operation' => $operation
                ];
            }
        }
        
        // Find conflicts
        foreach ($tables as $table => $operations) {
            if (count($operations) > 1) {
                $creates = array_filter($operations, fn($op) => $op['operation'] === 'create');
                $drops = array_filter($operations, fn($op) => $op['operation'] === 'drop');
                
                if (count($creates) > 1) {
                    $conflicts[] = [
                        'type' => 'duplicate_create',
                        'table' => $table,
                        'files' => array_column($creates, 'file')
                    ];
                }
            }
        }
        
        return $conflicts;
    }

    private function findDuplicateTableCreations($files)
    {
        $duplicates = [];
        $tableCreations = [];
        
        foreach ($files as $file) {
            $content = File::get(database_path("migrations/{$file}"));
            
            if (preg_match_all('/Schema::create\([\'"]([^\'"]+)/', $content, $matches)) {
                foreach ($matches[1] as $table) {
                    if (!isset($tableCreations[$table])) {
                        $tableCreations[$table] = [];
                    }
                    $tableCreations[$table][] = $file;
                }
            }
        }
        
        foreach ($tableCreations as $table => $files) {
            if (count($files) > 1) {
                $duplicates[] = [
                    'table' => $table,
                    'files' => $files
                ];
            }
        }
        
        return $duplicates;
    }

    private function analyzeSeeders()
    {
        $seederPath = database_path('seeders');
        $files = collect(File::files($seederPath))->map(fn($file) => $file->getFilename());
        
        // Check DatabaseSeeder references
        $databaseSeeder = File::get(database_path('seeders/DatabaseSeeder.php'));
        $referenced = [];
        
        preg_match_all('/\$this->call\(([^)]+)::class\)/', $databaseSeeder, $matches);
        foreach ($matches[1] as $seeder) {
            $referenced[] = $seeder . '.php';
        }
        
        $unused = $files->filter(fn($file) => 
            $file !== 'DatabaseSeeder.php' && 
            !in_array($file, $referenced)
        );

        $this->displaySeederResults($unused, $referenced, $files->count());
    }

    private function displayMigrationResults($unused, $conflicts, $duplicates, $ranCount, $totalCount)
    {
        $this->newLine();
        $this->info("📊 Migration Analysis Results:");
        $this->line("Total migrations: {$totalCount}");
        $this->line("Ran migrations: {$ranCount}");
        
        if ($unused->isNotEmpty()) {
            $this->warn("⚠️  Unused migrations ({$unused->count()}):");
            foreach ($unused as $file) {
                $this->line("  - {$file}");
            }
        } else {
            $this->info("✅ No unused migrations");
        }

        if (!empty($conflicts)) {
            $this->error("❌ Migration conflicts:");
            foreach ($conflicts as $conflict) {
                $this->line("  Table '{$conflict['table']}' created in multiple files:");
                foreach ($conflict['files'] as $file) {
                    $this->line("    - {$file}");
                }
            }
        } else {
            $this->info("✅ No migration conflicts");
        }

        if (!empty($duplicates)) {
            $this->error("❌ Duplicate table creations:");
            foreach ($duplicates as $duplicate) {
                $this->line("  Table '{$duplicate['table']}':");
                foreach ($duplicate['files'] as $file) {
                    $this->line("    - {$file}");
                }
            }
        } else {
            $this->info("✅ No duplicate table creations");
        }
    }

    private function displaySeederResults($unused, $referenced, $totalCount)
    {
        $this->newLine();
        $this->info("🌱 Seeder Analysis Results:");
        $this->line("Total seeders: {$totalCount}");
        $this->line("Referenced in DatabaseSeeder: " . count($referenced));
        
        if ($unused->isNotEmpty()) {
            $this->warn("⚠️  Unused seeders ({$unused->count()}):");
            foreach ($unused as $file) {
                $this->line("  - {$file}");
            }
        } else {
            $this->info("✅ All seeders are referenced");
        }
    }
}