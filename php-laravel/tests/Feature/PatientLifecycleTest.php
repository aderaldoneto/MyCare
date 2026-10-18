<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_patients_with_pagination_and_excludes_deleted_patients(): void
    {
        $token = $this->tokenForUser();
        Patient::factory()->create(['name' => 'Bruno Souza']);
        Patient::factory()->create(['name' => 'Ana Silva']);
        Patient::factory()->create(['name' => 'Paciente removido'])->delete();

        $this->withToken($token)
            ->getJson('/api/patients')
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('last_page', 1)
            ->assertJsonPath('data.0.name', 'Ana Silva')
            ->assertJsonMissing(['name' => 'Paciente removido']);
    }

    public function test_authenticated_user_can_soft_delete_a_patient(): void
    {
        $token = $this->tokenForUser();
        $patient = Patient::factory()->create();

        $this->withToken($token)
            ->deleteJson("/api/patients/{$patient->id}")
            ->assertOk()
            ->assertJson(['message' => 'Paciente excluído com sucesso.']);

        $this->assertSoftDeleted('patients', ['id' => $patient->id]);

        $this->withToken($token)
            ->getJson("/api/patients/{$patient->id}")
            ->assertNotFound();

        $this->withToken($token)
            ->getJson('/api/patients')
            ->assertOk()
            ->assertJsonPath('total', 0);
    }

    public function test_patient_deletion_requires_authentication(): void
    {
        $patient = Patient::factory()->create();

        $this->deleteJson("/api/patients/{$patient->id}")->assertUnauthorized();

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'deleted_at' => null,
        ]);
    }

    public function test_update_rejects_invalid_patient_data_without_changing_the_record(): void
    {
        $token = $this->tokenForUser();
        $patient = Patient::factory()->create([
            'name' => 'Ana Silva',
            'cpf' => '625.189.530-60',
            'rg' => '12.345.678',
            'birth_date' => '1996-07-20',
            'sex' => 'f',
            'phone' => '(11) 99999-0000',
            'email' => 'ana@example.com',
        ]);

        $this->withToken($token)
            ->putJson("/api/patients/{$patient->id}", [
                'name' => '',
                'cpf' => '625.189.530-60',
                'rg' => '12.345.678',
                'birth_date' => '2999-01-01',
                'sex' => 'x',
                'phone' => '(11) 99999-0000',
                'email' => 'not-an-email',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'birth_date', 'sex', 'email']);

        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'name' => 'Ana Silva',
            'birth_date' => '1996-07-20',
        ]);
    }

    private function tokenForUser(): string
    {
        return User::factory()->create()->createToken('test')->plainTextToken;
    }
}
