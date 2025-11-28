<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('tickets', 'assigned_to')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->foreignId('assigned_to')->nullable()->after('organization_id')->constrained('users')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tickets', 'assigned_to')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropForeign(['assigned_to']);
                $table->dropColumn('assigned_to');
            });
        }
    }
};
