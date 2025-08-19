<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Read the base.json file
        $jsonPath = base_path('base.json');

        if (!File::exists($jsonPath)) {
            $this->command->error('base.json file not found!');
            return;
        }

        $regions = json_decode(File::get($jsonPath), true);

        if (!$regions) {
            $this->command->error('Invalid JSON format in base.json');
            return;
        }

        $this->command->info('Starting to seed regions data...');

        // Prepare batch insert data
        $batchData = [];
        $batchSize = 1000;

        foreach ($regions as $region) {
            $code = $region['code'];
            $name = $region['name'];

            // Determine level based on code structure
            $level = $this->determineLevel($code);

            // Determine parent code
            $parentCode = $this->getParentCode($code, $level);

            $batchData[] = [
                'code' => $code,
                'name' => $name,
                'level' => $level,
                'parent_code' => $parentCode,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert batch when reaching batch size
            if (count($batchData) >= $batchSize) {
                DB::table('regions')->insert($batchData);
                $this->command->info('Inserted ' . count($batchData) . ' regions...');
                $batchData = [];
            }
        }

        // Insert remaining data
        if (!empty($batchData)) {
            DB::table('regions')->insert($batchData);
            $this->command->info('Inserted remaining ' . count($batchData) . ' regions...');
        }

        $this->command->info('Region seeding completed successfully!');
    }

    /**
     * Determine the administrative level based on code structure
     *
     * @param string $code
     * @return int
     */
    private function determineLevel(string $code): int
    {
        // Count dots to determine level
        $dotCount = substr_count($code, '.');

        // Level 1: Province (e.g., "11")
        // Level 2: Regency/City (e.g., "11.01")
        // Level 3: District (e.g., "11.01.01")
        // Level 4: Village (e.g., "11.01.01.2001")

        return $dotCount + 1;
    }

    /**
     * Get parent code based on current code and level
     *
     * @param string $code
     * @param int $level
     * @return string|null
     */
    private function getParentCode(string $code, int $level): ?string
    {
        if ($level === 1) {
            return null; // Province has no parent
        }

        $parts = explode('.', $code);
        array_pop($parts); // Remove last part

        return implode('.', $parts);
    }
}
