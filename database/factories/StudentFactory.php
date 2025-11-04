<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'date_of_birth' => $this->faker->date('Y-m-d', '-18 years'),
            'password' => bcrypt('password'),
            'role' => 'student',
            'student_id' => $this->faker->unique()->numerify('STU#####'),
            'bio' => $this->faker->sentence,
            'notification_preferences' => ['email' => true, 'sms' => false],
            'privacy_settings' => ['public_profile' => true],
        ];
    }
}
