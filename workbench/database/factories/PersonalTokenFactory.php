<?php

namespace Workbench\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
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
            'type' => $this->faker->randomElement(['type-1', 'type-2', 'type-3']),
            'expires_at' => Carbon::now(),
            'used_at' => $this->faker->randomElement([Carbon::now(), null]),
        ];
    }

    /**
     * Indicate that the token has a specific owner.
     */
    public function owner(Model $owner): static
    {
        return $this->state(fn () => [
            'owner_id' => $owner->id,
            'owner_type' => get_class($owner),
        ]);
    }
}
