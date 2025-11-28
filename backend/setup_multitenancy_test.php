<?php
use App\Models\User;
use App\Models\Organization;
use App\Models\Ticket;
use Illuminate\Support\Facades\Hash;

try {
    $org1 = Organization::firstOrCreate(['name' => 'Org 1'], ['slug' => 'org-1']);
    $org2 = Organization::firstOrCreate(['name' => 'Org 2'], ['slug' => 'org-2']);

    $userA = User::firstOrCreate(
        ['email' => 'usera@org1.com'],
        ['name' => 'User A', 'password' => Hash::make('password'), 'organization_id' => $org1->id, 'role' => 'agent']
    );

    $userB = User::firstOrCreate(
        ['email' => 'userb@org2.com'],
        ['name' => 'User B', 'password' => Hash::make('password'), 'organization_id' => $org2->id, 'role' => 'agent']
    );

    $ticket = Ticket::forceCreate([
        'subject' => 'Org 1 Ticket',
        'description' => 'Secret stuff',
        'status' => 'open',
        'priority' => 'high',
        'requester_id' => $userA->id,
        'organization_id' => $org1->id,
        'assignee_id' => null,
        'sla_due_date' => now()->addDays(2),
        'sla_breached' => false
    ]);

    $tokenB = $userB->createToken('test-token')->plainTextToken;

    echo json_encode([
        'ticket_id' => $ticket->id,
        'user_b_token' => $tokenB
    ]);

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
exit(0);
