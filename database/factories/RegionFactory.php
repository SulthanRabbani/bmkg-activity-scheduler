<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Region>
 */
class RegionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $level = $this->faker->numberBetween(1, 4);
        $code = $this->generateCode($level);

        return [
            'code' => $code,
            'name' => $this->faker->city(),
            'level' => $level,
            'parent_code' => $level > 1 ? $this->generateParentCode($code) : null,
        ];
    }

    /**
     * Generate a region code based on level
     */
    private function generateCode(int $level): string
    {
        $parts = [];

        for ($i = 1; $i <= $level; $i++) {
            if ($i === 4) {
                $parts[] = $this->faker->numberBetween(2001, 9999);
            } else {
                $parts[] = str_pad($this->faker->numberBetween(1, 99), 2, '0', STR_PAD_LEFT);
            }
        }

        return implode('.', $parts);
    }

    /**
     * Generate parent code from current code
     */
    private function generateParentCode(string $code): string
    {
        $parts = explode('.', $code);
        array_pop($parts);

        return implode('.', $parts);
    }
}
