<?php

namespace Egmond\InertiaTables\Tests\Database\Factories;

use Egmond\InertiaTables\Tests\Database\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = $this->faker->words(2, true);
        
        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->optional(0.7)->sentence(),
            'is_active' => $this->faker->boolean(85),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => $this->faker->paragraph(),
        ]);
    }
}