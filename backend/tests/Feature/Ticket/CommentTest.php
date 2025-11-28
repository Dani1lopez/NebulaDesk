<?php

namespace Tests\Feature\Ticket;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\Comment;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_add_comment_to_ticket()
    {
        $organization = Organization::create(['name' => 'Acme Corp']);
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $ticket = Ticket::create([
            'subject' => 'Ticket 1',
            'description' => 'Desc 1',
            'status' => 'open',
            'priority' => 'low',
            'requester_id' => $user->id,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($user);

        $response = $this->postJson("/api/tickets/{$ticket->id}/comments", [
            'content' => 'This is a comment',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'comment' => ['id', 'content', 'created_at']
            ]);

        $this->assertDatabaseHas('comments', ['content' => 'This is a comment']);
    }

    public function test_authenticated_user_can_list_comments()
    {
        $organization = Organization::create(['name' => 'Acme Corp']);
        $user = User::factory()->create(['organization_id' => $organization->id]);
        $ticket = Ticket::create([
            'subject' => 'Ticket 1',
            'description' => 'Desc 1',
            'status' => 'open',
            'priority' => 'low',
            'requester_id' => $user->id,
            'organization_id' => $organization->id,
        ]);

        Comment::create([
            'content' => 'First comment',
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        $response = $this->getJson("/api/tickets/{$ticket->id}/comments");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'comments')
            ->assertJsonFragment(['content' => 'First comment']);
    }
}
