<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_settlements', function (Blueprint $table) {
            $table->string('transfer_proof_path')->nullable()->after('transfer_notes');
            $table->string('owner_account_number')->nullable()->after('transfer_proof_path');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_settlements', function (Blueprint $table) {
            $table->dropColumn(['transfer_proof_path', 'owner_account_number']);
        });
    }
};