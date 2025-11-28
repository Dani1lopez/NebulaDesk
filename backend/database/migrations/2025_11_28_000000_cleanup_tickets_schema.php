<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // 1. Unificar assigned_to -> assignee_id
            if (Schema::hasColumn('tickets', 'assigned_to') && !Schema::hasColumn('tickets', 'assignee_id')) {
                $table->renameColumn('assigned_to', 'assignee_id');
            } elseif (Schema::hasColumn('tickets', 'assigned_to') && Schema::hasColumn('tickets', 'assignee_id')) {
                // Si ambos existen, copiamos datos de assigned_to a assignee_id donde sea nulo
                DB::statement('UPDATE tickets SET assignee_id = assigned_to WHERE assignee_id IS NULL AND assigned_to IS NOT NULL');
                
                // Drop foreign key first to support SQLite
                $table->dropForeign(['assigned_to']);
                $table->dropColumn('assigned_to');
            }

            // 2. Unificar sla_deadline -> sla_due_date
            if (Schema::hasColumn('tickets', 'sla_deadline') && !Schema::hasColumn('tickets', 'sla_due_date')) {
                $table->renameColumn('sla_deadline', 'sla_due_date');
            } elseif (Schema::hasColumn('tickets', 'sla_deadline') && Schema::hasColumn('tickets', 'sla_due_date')) {
                DB::statement('UPDATE tickets SET sla_due_date = sla_deadline WHERE sla_due_date IS NULL AND sla_deadline IS NOT NULL');
                $table->dropColumn('sla_deadline');
            }
        });
    }

    public function down(): void
    {
        // Reversión simplificada (no restaura datos perdidos)
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->constrained('users');
            }
             if (!Schema::hasColumn('tickets', 'sla_deadline')) {
                $table->timestamp('sla_deadline')->nullable();
            }
        });
    }
};
