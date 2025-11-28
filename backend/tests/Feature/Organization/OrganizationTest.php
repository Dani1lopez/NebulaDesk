<?php

namespace Tests\Feature\Organization;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_organization()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/organizations', [
            'name' => 'Acme Corp',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'organization' => ['id', 'name']
            ]);

        $this->assertDatabaseHas('organizations', ['name' => 'Acme Corp']);
    }

    public function test_unauthenticated_user_cannot_create_organization()
    {
        $response = $this->postJson('/api/organizations', [
            'name' => 'Acme Corp',
        ]);

        $response->assertStatus(401);
    }

    public function test_organization_name_must_be_unique()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        \App\Models\Organization::create(['name' => 'Acme Corp']);

        $response = $this->postJson('/api/organizations', [
            'name' => 'Acme Corp',
        ]);

        $response->assertStatus(422);
    }
}
