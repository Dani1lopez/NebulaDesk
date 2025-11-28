<?php

namespace App\Jobs;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SlaBreachCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info("SlaBreachCheckJob: Starting check...");
        
        $count = 0;
        
        // Procesamos en chunks para no saturar memoria en multi-tenant
        Ticket::whereNotIn('status', ['closed', 'resolved'])
            ->where('sla_breached', false)
            ->whereNotNull('sla_due_date')
            ->where('sla_due_date', '<', now())
            ->chunkById(100, function ($tickets) use (&$count) {
                foreach ($tickets as $ticket) {
                    try {
                        $ticket->update(['sla_breached' => true]);
                        $count++;
                        Log::info("SLA Breached for Ticket ID: {$ticket->id}");
                        // Aquí podríamos disparar un evento: event(new TicketSlaBreached($ticket));
                    } catch (\Exception $e) {
                        Log::error("Failed to update SLA breach for Ticket ID: {$ticket->id}. Error: " . $e->getMessage());
                    }
                }
            });

        Log::info("SlaBreachCheckJob: Finished. Total breached tickets processed: {$count}");
    }
}
