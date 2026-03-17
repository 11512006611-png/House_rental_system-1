<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('owner_share_amount', 10, 2)->default(0)->after('commission_amount');
            $table->string('transaction_id', 120)->nullable()->after('payment_date');
            $table->string('payment_proof_path')->nullable()->after('transaction_id');
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending')->after('status');
            $table->timestamp('verified_at')->nullable()->after('verification_status');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'owner_share_amount',
                'transaction_id',
                'payment_proof_path',
                'verification_status',
                'verified_at',
            ]);
        });
    }
};
