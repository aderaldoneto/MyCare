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
            ['created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['created_at' => $now, 'updated_at' => $now, 'deleted_at' => $now],
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
}
