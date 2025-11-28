<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_profile()
    {
        $organization = Organization::create(['name' => 'Acme Corp']);
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $this->actingAs($user);

        $response = $this->putJson('/api/user/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email']
            ])
            ->assertJsonFragment(['name' => 'Updated Name']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_user_cannot_update_email_to_existing_one()
    {
        $organization = Organization::create(['name' => 'Acme Corp']);
        $user1 = User::factory()->create(['organization_id' => $organization->id, 'email' => 'user1@example.com']);
        $user2 = User::factory()->create(['organization_id' => $organization->id, 'email' => 'user2@example.com']);

        $this->actingAs($user1);

        $response = $this->putJson('/api/user/profile', [
            'email' => 'user2@example.com',
        ]);

        $response->assertStatus(422);
    }
}
