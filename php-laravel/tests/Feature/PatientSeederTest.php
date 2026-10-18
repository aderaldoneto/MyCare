<?php

namespace Tests\Feature;

use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_thirty_patients_and_does_not_duplicate_them(): void
    {
        $this->seed(\Database\Seeders\PatientSeeder::class);

        $this->assertSame(30, Patient::query()->count());
        $this->assertSame(30, Patient::query()->distinct('cpf')->count('cpf'));

        $this->seed(\Database\Seeders\PatientSeeder::class);

        $this->assertSame(30, Patient::query()->count());
    }
}
