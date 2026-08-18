<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'status' => 'active',
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withRole(string $slug): static
    {
        return $this->state(fn () => [
            'role_id' => Role::where('slug', $slug)->first()?->id,
        ]);
    }

    public function forSchool(School|int $school): static
    {
        $schoolId = $school instanceof School ? $school->id : $school;
        return $this->state(fn () => [
            'school_id' => $schoolId,
        ]);
    }

    public function superAdmin(): static { return $this->withRole('super_admin'); }
    public function schoolOwner(): static { return $this->withRole('school_owner'); }
    public function schoolAdmin(): static { return $this->withRole('school_admin'); }
    public function teacher(): static { return $this->withRole('teacher'); }
    public function student(): static { return $this->withRole('student'); }
    public function parent(): static { return $this->withRole('parent'); }
}