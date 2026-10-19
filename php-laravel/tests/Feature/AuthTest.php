<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_logout_with_a_sanctum_token(): void
    {
        User::factory()->create([
            'email' => 'admin@mycare.com',
            'password' => Hash::make('123456789'),
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'admin@mycare.com',
            'password' => '123456789',
        ]);

        $loginResponse
            ->assertOk()
            ->assertJsonPath('user.email', 'admin@mycare.com')
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);

        $token = $loginResponse->json('token');

        $this->withToken($token)->getJson('/api/user')->assertOk();
        $this->withToken($token)->postJson('/api/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
        Auth::forgetGuards();
        $this->withToken($token)->getJson('/api/user')->assertUnauthorized();
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@mycare.com',
            'password' => Hash::make('123456789'),
        ]);

        $this->postJson('/api/login', [
            'email' => 'admin@mycare.com',
            'password' => 'incorrect-password',
        ])
            ->assertUnauthorized()
            ->assertJson(['message' => 'E-mail ou senha inválidos.']);
    }
}
