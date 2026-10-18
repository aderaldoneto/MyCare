<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        $faker = fake('pt_BR');

        return [
            'name' => $faker->name(),
            'cpf' => $faker->unique()->cpf(),
            'birth_date' => $faker->dateTimeBetween('-90 years', '-18 years')->format('Y-m-d'),
            'phone' => $faker->cellphoneNumber(),
            'email' => $faker->unique()->safeEmail(),
            'rg' => $faker->rg(),
            'sex' => $faker->randomElement(['m', 'f']),
            'description' => $faker->optional()->text(200),
        ];
    }
}
