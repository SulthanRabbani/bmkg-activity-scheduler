<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Region;

class TestRegionSearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:region-search {query}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test region search functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = $this->argument('query');

        $this->info("Searching for regions with query: '{$query}'");

        $regions = Region::whereIn('level', [3, 4])
            ->where('name', 'like', '%' . $query . '%')
            ->with('parent.parent.parent')
            ->limit(10)
            ->get();

        if ($regions->count() > 0) {
            $this->info("Found {$regions->count()} regions:");

            foreach ($regions as $region) {
                $this->line("  - {$region->name} ({$region->level_name})");
                $this->line("    Path: {$region->full_path}");
                $this->line("    Code: {$region->code}");
                $this->line("");
            }
        } else {
            $this->warn("No regions found for query: '{$query}'");
        }

        return 0;
    }
}
