<?php

namespace Tests\Feature\Ticket;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\Ticket;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_ticket()
    {
        $organization = Organization::create(['name' => 'Acme Corp']);
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $this->actingAs($user);

        $response = $this->postJson('/api/tickets', [
            'subject' => 'Help needed',
            'description' => 'I cannot login',
            'priority' => 'high',
            'organization_id' => $organization->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'ticket' => ['id', 'subject', 'status']
            ]);

        $this->assertDatabaseHas('tickets', ['subject' => 'Help needed']);
    }

    public function test_authenticated_user_can_list_tickets()
    {
        $organization = Organization::create(['name' => 'Acme Corp']);
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $this->actingAs($user);

        Ticket::create([
            'subject' => 'Ticket 1',
            'description' => 'Desc 1',
            'status' => 'open',
            'priority' => 'low',
            'requester_id' => $user->id,
            'organization_id' => $organization->id,
        ]);

        $response = $this->getJson('/api/tickets?organization_id=' . $organization->id);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'tickets')
            ->assertJsonFragment(['subject' => 'Ticket 1']);
    }

    public function test_authenticated_user_can_view_ticket()
    {
        $organization = Organization::create(['name' => 'Acme Corp']);
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $this->actingAs($user);

        $ticket = Ticket::create([
            'subject' => 'Ticket 1',
            'description' => 'Desc 1',
            'status' => 'open',
            'priority' => 'low',
            'requester_id' => $user->id,
            'organization_id' => $organization->id,
        ]);

        $response = $this->getJson("/api/tickets/{$ticket->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'ticket' => ['id', 'subject', 'description']
            ])
            ->assertJsonFragment(['subject' => 'Ticket 1']);
    }
}
