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
    ];
  }
}
