<?php

namespace Kettasoft\Filterable\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Kettasoft\Filterable\Tests\Models\User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'is_blocked' => $this->faker->boolean,
            'platform' => $this->faker->randomElement(['web', 'ios', 'android']),
            'password' => bcrypt('password'), // or use Hash::make('password')
        ];
    }
}
