<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PatientCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_active_patient_count(): void
    {
        $user = User::factory()->create();
        $now = now();

        DB::table('patients')->insert([
            [
                'name' => 'Alfredo Rhiel Madsen',
                'cpf' => '625.189.530-60',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'name' => 'Justin Baptista',
                'cpf' => '552.777.100-19',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ],
            [
                'name' => 'Paciente removido',
                'cpf' => '796.984.710-22',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => $now,
            ],
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/patients/count')
            ->assertOk()
            ->assertJson(['count' => 2]);
    }

    public function test_patient_count_requires_authentication(): void
    {
        $this->getJson('/api/patients/count')->assertUnauthorized();
    }

    public function test_authenticated_user_can_search_patients_by_name_or_cpf(): void
    {
        $user = User::factory()->create();
        $now = now();

        DB::table('patients')->insert([
            [
                'name' => 'Alfredo Rhiel Madsen',
                'cpf' => '625.189.530-60',
                'phone' => '(11) 92853-5718',
                'birth_date' => '1996-07-20',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Justin Baptista',
                'cpf' => '552.777.100-19',
                'phone' => '(11) 93078-1380',
                'birth_date' => '1984-12-18',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/patients?search=alfredo')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.name', 'Alfredo Rhiel Madsen')
            ->assertJsonPath('data.0.cpf', '625.189.530-60')
            ->assertJsonPath('data.0.birth_date', '1996-07-20');
    }

    public function test_patient_list_requires_authentication(): void
    {
        $this->getJson('/api/patients')->assertUnauthorized();
    }
}
