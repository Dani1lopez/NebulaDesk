<?php
try {
    $u = App\Models\User::first();
    $o = App\Models\Organization::first();
    if (!$u || !$o) { echo "User or Org missing\n"; exit(1); }
    
    // Using forceCreate because requester_id is missing from fillable
    $t = App\Models\Ticket::forceCreate([
        'subject' => 'SLA Test Ticket',
        'description' => 'Testing SLA Breach',
        'status' => 'open',
        'priority' => 'high',
        'requester_id' => $u->id,
        'organization_id' => $o->id,
        'assignee_id' => null,
        'sla_due_date' => now()->subDays(2),
        'sla_breached' => false
    ]);
    echo "Ticket Created: " . $t->id . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
exit(0);
