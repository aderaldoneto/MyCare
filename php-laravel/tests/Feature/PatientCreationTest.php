<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_patient(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/patients', [
            'name' => 'Ana da Silva',
            'cpf' => '625.189.530-60',
            'rg' => '12.345.678-9',
            'birth_date' => '1996-07-20',
            'sex' => 'f',
            'phone' => '(11) 99999-0000',
            'email' => 'ana@example.com',
            'description' => 'Acompanhamento clínico.',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('name', 'Ana da Silva')
            ->assertJsonPath('cpf', '625.189.530-60')
            ->assertJsonPath('rg', '12.345.678-9')
            ->assertJsonPath('sex', 'f')
            ->assertJsonPath('description', 'Acompanhamento clínico.');

        $this->assertDatabaseHas('patients', [
            'cpf' => '625.189.530-60',
            'email' => 'ana@example.com',
        ]);
    }

    public function test_patient_creation_requires_authentication(): void
    {
        $this->postJson('/api/patients', [
            'name' => 'Ana da Silva',
            'cpf' => '625.189.530-60',
        ])->assertUnauthorized();
    }

    public function test_patient_creation_rejects_duplicate_cpf_and_invalid_sex(): void
    {
        Patient::factory()->create(['cpf' => '625.189.530-60']);
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/patients', [
                'name' => 'Ana da Silva',
                'cpf' => '625.189.530-60',
                'sex' => 'x',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cpf', 'sex']);
    }

    public function test_authenticated_user_can_view_and_update_a_patient(): void
    {
        $patient = Patient::factory()->create([
            'name' => 'Ana da Silva',
            'cpf' => '625.189.530-60',
            'rg' => '12.345.678',
            'birth_date' => '1996-07-20',
            'sex' => 'f',
            'phone' => '(11) 9 9999-0000',
            'email' => 'ana@example.com',
            'description' => 'Acompanhamento inicial.',
        ]);
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson("/api/patients/{$patient->id}")
            ->assertOk()
            ->assertJsonPath('id', $patient->id)
            ->assertJsonPath('description', 'Acompanhamento inicial.');

        $this->withToken($token)
            ->putJson("/api/patients/{$patient->id}", [
                'name' => 'Ana da Silva Atualizada',
                'cpf' => '625.189.530-60',
                'rg' => '98.765.432',
                'birth_date' => '1996-07-20',
                'sex' => 'f',
                'phone' => '(11) 9 8888-0000',
                'email' => 'ana@example.com',
                'description' => 'Acompanhamento atualizado.',
            ])
            ->assertOk()
            ->assertJsonPath('name', 'Ana da Silva Atualizada')
            ->assertJsonPath('description', 'Acompanhamento atualizado.');

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'name' => 'Ana da Silva Atualizada',
            'phone' => '(11) 9 8888-0000',
        ]);
    }

    public function test_patient_details_and_update_require_authentication(): void
    {
        $patient = Patient::factory()->create();

        $this->getJson("/api/patients/{$patient->id}")->assertUnauthorized();
        $this->putJson("/api/patients/{$patient->id}", [])->assertUnauthorized();
    }
}
