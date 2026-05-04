<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspection_requests', function (Blueprint $table) {
            $table->enum('tenant_decision', ['stay', 'move_out'])->nullable()->after('handled_at');
            $table->text('tenant_decision_message')->nullable()->after('tenant_decision');
            $table->timestamp('tenant_decision_at')->nullable()->after('tenant_decision_message');
        });
    }

    public function down(): void
    {
        Schema::table('inspection_requests', function (Blueprint $table) {
            $table->dropColumn(['tenant_decision', 'tenant_decision_message', 'tenant_decision_at']);
        });
    }
};
