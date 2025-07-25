<?php

namespace Egmond\InertiaTables\Tests\Database\Factories;

use Egmond\InertiaTables\Tests\Database\Models\Comment;
use Egmond\InertiaTables\Tests\Database\Models\Post;
use Egmond\InertiaTables\Tests\Database\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'content' => $this->faker->paragraph(),
            'is_approved' => $this->faker->boolean(70),
            'user_id' => User::factory(),
            'post_id' => Post::factory(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => true,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => false,
        ]);
    }

    public function long(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->paragraphs(3, true),
        ]);
    }

    public function short(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->sentence(),
        ]);
    }
}
