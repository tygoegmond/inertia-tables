<?php

namespace Egmond\InertiaTables\Tests\Database\Factories;

use Egmond\InertiaTables\Tests\Database\Models\Category;
use Egmond\InertiaTables\Tests\Database\Models\Post;
use Egmond\InertiaTables\Tests\Database\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = $this->faker->sentence();
        $content = $this->faker->paragraphs(5, true);
        
        return [
            'title' => rtrim($title, '.'),
            'slug' => Str::slug($title),
            'content' => $content,
            'excerpt' => $this->faker->optional(0.8)->text(200),
            'status' => $this->faker->randomElement(['draft', 'published', 'archived', 'pending']),
            'views' => $this->faker->numberBetween(0, 10000),
            'rating' => $this->faker->optional(0.6)->randomFloat(2, 1, 5),
            'tags' => $this->faker->optional(0.7)->randomElements(
                ['php', 'laravel', 'javascript', 'react', 'vue', 'mysql', 'api', 'testing', 'tutorial', 'guide'],
                $this->faker->numberBetween(1, 4)
            ),
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'published_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }

    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'views' => $this->faker->numberBetween(5000, 50000),
            'rating' => $this->faker->randomFloat(2, 4, 5),
        ]);
    }

    public function withTags(): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => $this->faker->randomElements(
                ['php', 'laravel', 'javascript', 'react', 'vue', 'mysql', 'api', 'testing', 'tutorial', 'guide'],
                $this->faker->numberBetween(2, 5)
            ),
        ]);
    }
}