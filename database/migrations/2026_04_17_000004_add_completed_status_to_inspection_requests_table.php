<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inspection_requests')) {
            return;
        }

        DB::statement("ALTER TABLE inspection_requests MODIFY status ENUM('pending', 'confirmed', 'completed', 'rescheduled', 'cancelled', 'rejected') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('inspection_requests')) {
            return;
        }

        DB::table('inspection_requests')
            ->where('status', 'completed')
            ->update(['status' => 'confirmed']);

        DB::statement("ALTER TABLE inspection_requests MODIFY status ENUM('pending', 'confirmed', 'rescheduled', 'cancelled', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};
