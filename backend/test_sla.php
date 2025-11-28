$user = App\Models\User::first();
$org = App\Models\Organization::first();
if (!$user || !$org) { echo "No user or org found\n"; exit; }
$ticket = App\Models\Ticket::create([
    'subject' => 'SLA Test Ticket ' . time(),
    'description' => 'Testing SLA Breach',
    'priority' => 'high',
    'status' => 'open',
    'requester_id' => $user->id,
    'organization_id' => $org->id,
    'sla_due_date' => now()->subHour(),
    'sla_breached' => false
]);
echo "Created Ticket ID: " . $ticket->id . "\n";
(new App\Jobs\SlaBreachCheckJob)->handle();
$ticket->refresh();
echo "Ticket SLA Breached: " . ($ticket->sla_breached ? 'YES' : 'NO') . "\n";
