<?php

namespace Database\Factories;

use App\Models\AuthenticatedUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AuthenticatedUserFactory extends Factory
{
    // Specify the model the factory is for
    protected $model = AuthenticatedUser::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => bcrypt('password'), // Default password for testing
            'remember_token' => Str::random(10),
            // Add other fields specific to your AuthenticatedUser model here
        ];
    }
}
