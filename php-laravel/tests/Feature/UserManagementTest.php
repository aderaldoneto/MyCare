<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_active_user_count(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);
        User::factory()->count(2)->create();
        User::factory()->create()->delete();
        $token = $admin->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/users/count')
            ->assertOk()
            ->assertJson(['count' => 3]);
    }

    public function test_user_count_requires_authentication_but_not_admin_role(): void
    {
        $this->getJson('/api/users/count')->assertUnauthorized();

        $user = User::factory()->create(['role' => Role::Doctor]);
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/users/count')
            ->assertOk();
    }

    public function test_non_admin_can_list_create_view_update_and_delete_users(): void
    {
        $admin = User::factory()->create(['role' => Role::Doctor]);
        $target = User::factory()->create([
            'name' => 'Maria da Silva',
            'email' => 'maria@example.com',
            'role' => Role::Doctor,
            'password' => 'old-password',
        ]);
        $token = $admin->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/users?search=maria')
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.email', 'maria@example.com')
            ->assertJsonMissingPath('data.0.password');

        $created = $this->withToken($token)
            ->postJson('/api/users', [
                'name' => 'João da Silva',
                'email' => 'joao@example.com',
                'role' => Role::Receptionist->value,
                'password' => 'password-123',
            ])
            ->assertCreated()
            ->assertJsonPath('role', Role::Receptionist->value);

        $createdId = $created->json('id');

        $this->withToken($token)
            ->getJson("/api/users/{$target->id}")
            ->assertOk()
            ->assertJsonPath('name', 'Maria da Silva')
            ->assertJsonMissingPath('password');

        $this->withToken($token)
            ->putJson("/api/users/{$target->id}", [
                'name' => 'Maria Silva Atualizada',
                'email' => 'maria@example.com',
                'role' => Role::Manager->value,
                'password' => null,
            ])
            ->assertOk()
            ->assertJsonPath('role', Role::Manager->value);

        $this->assertTrue(Hash::check('old-password', $target->fresh()->password));

        $this->withToken($token)
            ->deleteJson("/api/users/{$createdId}")
            ->assertOk();

        $this->assertSoftDeleted('users', ['id' => $createdId]);
    }

    public function test_non_admin_can_manage_users(): void
    {
        $nonAdmin = User::factory()->create(['role' => Role::Doctor]);
        $token = $nonAdmin->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/users')
            ->assertOk();
    }

    public function test_admin_cannot_delete_self_or_remove_the_last_admin_role(): void
    {
        $admin = User::factory()->create(['role' => Role::Admin]);
        $token = $admin->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->deleteJson("/api/users/{$admin->id}")
            ->assertUnprocessable();

        $this->withToken($token)
            ->putJson("/api/users/{$admin->id}", [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => Role::Manager->value,
                'password' => null,
            ])
            ->assertUnprocessable();
    }

    public function test_user_management_requires_authentication(): void
    {
        $this->getJson('/api/users')->assertUnauthorized();
        $this->postJson('/api/users', [])->assertUnauthorized();
    }
}
