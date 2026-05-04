<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            // Add new fields for complaint workflow
            if (!Schema::hasColumn('maintenance_requests', 'needs_inspection')) {
                $table->boolean('needs_inspection')->default(false)->after('status')->comment('True if admin inspection is required');
            }
            if (!Schema::hasColumn('maintenance_requests', 'payment_responsibility')) {
                $table->enum('payment_responsibility', ['owner', 'tenant'])->nullable()->after('needs_inspection')->comment('Who pays for the repair');
            }
            if (!Schema::hasColumn('maintenance_requests', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('owner_response')->comment('Admin notes on the complaint');
            }
            if (!Schema::hasColumn('maintenance_requests', 'inspection_notes')) {
                $table->text('inspection_notes')->nullable()->after('admin_notes')->comment('Notes from inspection if performed');
            }
            if (!Schema::hasColumn('maintenance_requests', 'service_provider_assigned_at')) {
                $table->timestamp('service_provider_assigned_at')->nullable()->after('inspection_notes')->comment('When repair service was arranged');
            }
            if (!Schema::hasColumn('maintenance_requests', 'approved_for_repair_at')) {
                $table->timestamp('approved_for_repair_at')->nullable()->after('service_provider_assigned_at')->comment('When admin approved for repair');
            }
            if (!Schema::hasColumn('maintenance_requests', 'under_repair_at')) {
                $table->timestamp('under_repair_at')->nullable()->after('approved_for_repair_at')->comment('When repair started');
            }
        });
    }

    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropColumn([
                'needs_inspection',
                'payment_responsibility',
                'admin_notes',
                'inspection_notes',
                'service_provider_assigned_at',
                'approved_for_repair_at',
                'under_repair_at',
            ]);
        });
    }
};

