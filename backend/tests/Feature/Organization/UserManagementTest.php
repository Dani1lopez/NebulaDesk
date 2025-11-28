<?php

namespace Tests\Feature\Organization;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_invite_user_to_organization()
    {
        $organization = Organization::create(['name' => 'Acme Corp']);
        $admin = User::factory()->create(['organization_id' => $organization->id, 'role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->postJson('/api/organizations/users', [
            'name' => 'New Agent',
            'email' => 'agent@acme.com',
            'role' => 'agent',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'role']
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'agent@acme.com',
            'organization_id' => $organization->id,
        ]);
    }

    public function test_authenticated_user_can_list_organization_users()
    {
        $organization = Organization::create(['name' => 'Acme Corp']);
        $admin = User::factory()->create(['organization_id' => $organization->id, 'role' => 'admin']);
        $agent = User::factory()->create(['organization_id' => $organization->id, 'role' => 'agent']);

        $this->actingAs($admin);

        $response = $this->getJson('/api/organizations/users');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'users')
            ->assertJsonFragment(['email' => $admin->email])
            ->assertJsonFragment(['email' => $agent->email]);
    }
}
