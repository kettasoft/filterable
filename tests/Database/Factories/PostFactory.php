<?php

namespace Kettasoft\Filterable\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = \Kettasoft\Filterable\Tests\Models\Post::class;

  /**
   * Define the model's default state.
   *
   * @return array
   */
  public function definition()
  {
    return [
      'title' => $this->faker->word,
      'content' => $this->faker->text,
      'status' => $this->faker->randomElement(['active', 'pending', 'stopped']),
      'views' => $this->faker->numberBetween(0, 1000),
      'is_featured' => $this->faker->boolean,
      'description' => $this->faker->optional()->text,
      'tags' => $this->faker->optional()->randomElements(['php', 'laravel', 'javascript', 'vue'], $this->faker->numberBetween(0, 3)),
    ];
  }
}
