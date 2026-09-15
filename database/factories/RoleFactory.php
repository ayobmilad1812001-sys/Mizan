<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slug = fake()->unique()->lexify('role_??????');

        return [
            'name' => $slug,
            'slug' => $slug,
            'description' => null,
            'is_system' => false,
        ];
    }
}
