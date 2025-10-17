<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Workbench\App\Models\PersonalToken;

class PersonalTokenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = PersonalToken::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'token' => Str::random(40),
            'type' => $this->faker->randomElement(['type1', 'type2', 'type3']),
            'expires_at' => $this->faker->dateTime(),
            'used_at' => $this->faker->optional()->dateTime(),
        ];
    }

    /**
     * Set the owner for the factory.
     */
    public function owner(Model $owner): static
    {
        return $this->state(fn () => [
            'owner_id' => $owner->id,
            'owner_type' => get_class($owner),
        ]);
    }
}
