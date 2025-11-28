<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Índices compuestos para búsquedas frecuentes
            $table->index(['organization_id', 'status']); 
            $table->index(['organization_id', 'assignee_id']);
            
            // Índice para el Job de SLA
            $table->index(['status', 'sla_breached', 'sla_due_date'], 'idx_tickets_sla_check');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index(['organization_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['organization_id', 'status']);
            $table->dropIndex(['organization_id', 'assignee_id']);
            $table->dropIndex('idx_tickets_sla_check');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['organization_id', 'email']);
        });
    }
};
