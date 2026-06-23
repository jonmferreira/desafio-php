<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Cpf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->operator = User::factory()->create();
    }

    public function test_admin_can_list_users(): void
    {
        $this->actingAs($this->admin)
            ->getJson('/api/users')
            ->assertOk()
            ->assertJsonStructure(['data', 'total']);
    }

    public function test_operator_cannot_list_users(): void
    {
        $this->actingAs($this->operator)
            ->getJson('/api/users')
            ->assertForbidden();
    }

    public function test_unauthenticated_cannot_list_users(): void
    {
        $this->getJson('/api/users')->assertUnauthorized();
    }

    public function test_admin_can_create_user(): void
    {
        $cpf = Cpf::generate();

        $this->actingAs($this->admin)
            ->postJson('/api/users', [
                'name' => 'Novo Operador',
                'cpf' => $cpf,
                'email' => 'novo@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertCreated()
            ->assertJsonPath('email', 'novo@example.com');
    }

    public function test_create_user_rejects_invalid_cpf(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/users', [
                'name' => 'Test',
                'cpf' => '00000000000',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['cpf']);
    }

    public function test_create_user_rejects_duplicate_email(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/users', [
                'name' => 'Test',
                'cpf' => Cpf::generate(),
                'email' => $this->operator->email,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_create_user_rejects_short_password(): void
    {
        $this->actingAs($this->admin)
            ->postJson('/api/users', [
                'name' => 'Test',
                'cpf' => Cpf::generate(),
                'email' => 'short@example.com',
                'password' => '1234567',
                'password_confirmation' => '1234567',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_admin_can_update_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin)
            ->putJson("/api/users/{$user->id}", ['name' => 'Atualizado'])
            ->assertOk()
            ->assertJsonPath('name', 'Atualizado');
    }

    public function test_admin_can_delete_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->admin)
            ->deleteJson("/api/users/{$user->id}")
            ->assertNoContent();

        $this->assertModelMissing($user);
    }

    public function test_operator_cannot_view_another_user(): void
    {
        $other = User::factory()->create();

        $this->actingAs($this->operator)
            ->getJson("/api/users/{$other->id}")
            ->assertForbidden();
    }

    public function test_operator_can_view_own_profile(): void
    {
        $this->actingAs($this->operator)
            ->getJson("/api/users/{$this->operator->id}")
            ->assertOk()
            ->assertJsonPath('id', $this->operator->id);
    }
}
