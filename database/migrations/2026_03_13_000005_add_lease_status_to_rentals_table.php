<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->enum('lease_status', ['not_requested', 'requested', 'approved', 'rejected'])
                ->default('not_requested')
                ->after('status');
            $table->timestamp('lease_requested_at')->nullable()->after('lease_status');
            $table->timestamp('lease_reviewed_at')->nullable()->after('lease_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn(['lease_status', 'lease_requested_at', 'lease_reviewed_at']);
        });
    }
};
