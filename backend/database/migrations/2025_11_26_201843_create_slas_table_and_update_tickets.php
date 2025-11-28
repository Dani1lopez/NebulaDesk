<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('slas', function (Blueprint $table) {
            $table->id();
            $table->string('priority')->unique(); // low, medium, high, urgent
            $table->integer('response_time_hours');
            $table->integer('resolution_time_hours');
            $table->timestamps();
        });

        Schema::table('tickets', function (Blueprint $table) {
            // We already have sla_deadline from previous migration, but let's add sla_due_date as requested
            // and sla_breached.
            // If sla_deadline exists, we can drop it or just ignore it. Let's keep it for now to avoid issues.
            $table->timestamp('sla_due_date')->nullable()->after('status');
            $table->boolean('sla_breached')->default(false)->after('sla_due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['sla_due_date', 'sla_breached']);
        });

        Schema::dropIfExists('slas');
    }
};
