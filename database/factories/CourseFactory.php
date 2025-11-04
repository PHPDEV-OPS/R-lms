<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_code' => $this->faker->unique()->regexify('[A-Z]{3}[0-9]{3}'),
            'course_name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'credits' => $this->faker->numberBetween(1, 6),
            'price' => $this->faker->randomFloat(2, 100, 10000),
        ];
    }
}
