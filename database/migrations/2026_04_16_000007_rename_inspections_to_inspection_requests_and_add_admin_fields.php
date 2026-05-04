<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inspections') && ! Schema::hasTable('inspection_requests')) {
            Schema::rename('inspections', 'inspection_requests');
        }

        if (! Schema::hasTable('inspection_requests')) {
            return;
        }

        DB::table('inspection_requests')
            ->whereIn('status', ['approved', 'completed'])
            ->update(['status' => 'confirmed']);

        Schema::table('inspection_requests', function (Blueprint $table) {
            $table->text('admin_message')->nullable()->after('scheduled_at');
            $table->text('rejection_reason')->nullable()->after('admin_message');
            $table->foreignId('handled_by_admin_id')->nullable()->after('rejection_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable()->after('handled_by_admin_id');
        });

        DB::statement("ALTER TABLE inspection_requests MODIFY status ENUM('pending', 'confirmed', 'rescheduled', 'rejected') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        if (! Schema::hasTable('inspection_requests')) {
            return;
        }

        DB::statement("ALTER TABLE inspection_requests MODIFY status ENUM('pending', 'approved', 'rejected', 'completed') NOT NULL DEFAULT 'pending'");

        Schema::table('inspection_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('handled_by_admin_id');
            $table->dropColumn(['admin_message', 'rejection_reason', 'handled_at']);
        });

        Schema::rename('inspection_requests', 'inspections');
    }
};
