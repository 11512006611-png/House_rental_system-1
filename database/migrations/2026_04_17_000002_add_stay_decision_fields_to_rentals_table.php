<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->enum('stay_decision', ['yes', 'no'])->nullable()->after('lease_status');
            $table->text('stay_decision_message')->nullable()->after('stay_decision');
            $table->timestamp('stay_decision_at')->nullable()->after('stay_decision_message');
        });
    }

    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn(['stay_decision', 'stay_decision_message', 'stay_decision_at']);
        });
    }
};
