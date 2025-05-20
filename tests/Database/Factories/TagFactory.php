<?php

namespace Kettasoft\Filterable\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Kettasoft\Filterable\Tests\Models\Post;

class TagFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = \Kettasoft\Filterable\Tests\Models\Tag::class;

  /**
   * Define the model's default state.
   *
   * @return array
   */
  public function definition()
  {
    return [
      'name' => $this->faker->word,
      'post_id' => Post::inRandomOrder()->first('id')->getKey()
    ];
  }
}
