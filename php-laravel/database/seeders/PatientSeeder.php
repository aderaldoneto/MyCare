<?php

namespace Database\Seeders;

use App\Models\Patient;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        $remainingPatients = max(0, 30 - Patient::query()->count());

        Patient::factory()->count($remainingPatients)->create();
    }
}
