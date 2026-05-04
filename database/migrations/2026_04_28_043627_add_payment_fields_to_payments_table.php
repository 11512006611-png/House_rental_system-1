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
        Schema::table('payments', function (Blueprint $table) {
            // Add missing fields for payment tracking
            if (!Schema::hasColumn('payments', 'billing_month')) {
                $table->date('billing_month')->nullable()->after('payment_date');
            }
            if (!Schema::hasColumn('payments', 'payment_type')) {
                $table->enum('payment_type', ['first_month_rent', 'monthly_rent', 'security_deposit', 'refund'])->default('monthly_rent')->after('method');
            }
            if (!Schema::hasColumn('payments', 'verification_status')) {
                $table->enum('verification_status', ['pending', 'verified', 'failed'])->default('pending')->after('status');
            }
            if (!Schema::hasColumn('payments', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verification_status');
            }
            if (!Schema::hasColumn('payments', 'payment_proof_path')) {
                $table->string('payment_proof_path')->nullable()->after('transaction_reference');
            }
            if (!Schema::hasColumn('payments', 'rent_amount')) {
                $table->decimal('rent_amount', 10, 2)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('payments', 'security_deposit_amount')) {
                $table->decimal('security_deposit_amount', 10, 2)->nullable()->after('rent_amount');
            }
            if (!Schema::hasColumn('payments', 'service_fee_rate')) {
                $table->decimal('service_fee_rate', 5, 2)->nullable()->after('security_deposit_amount');
            }
            if (!Schema::hasColumn('payments', 'service_fee_amount')) {
                $table->decimal('service_fee_amount', 10, 2)->nullable()->after('service_fee_rate');
            }
            if (!Schema::hasColumn('payments', 'total_advance_amount')) {
                $table->decimal('total_advance_amount', 10, 2)->nullable()->after('service_fee_amount');
            }
            if (!Schema::hasColumn('payments', 'commission_rate')) {
                $table->decimal('commission_rate', 5, 2)->nullable()->after('total_advance_amount');
            }
            if (!Schema::hasColumn('payments', 'commission_amount')) {
                $table->decimal('commission_amount', 10, 2)->nullable()->after('commission_rate');
            }
            if (!Schema::hasColumn('payments', 'owner_share_amount')) {
                $table->decimal('owner_share_amount', 10, 2)->nullable()->after('commission_amount');
            }
            if (!Schema::hasColumn('payments', 'held_by_admin')) {
                $table->boolean('held_by_admin')->default(false)->after('owner_share_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop the added columns
            $columns = [
                'billing_month', 'payment_type', 'verification_status', 'verified_at',
                'payment_proof_path', 'rent_amount', 'security_deposit_amount',
                'service_fee_rate', 'service_fee_amount', 'total_advance_amount',
                'commission_rate', 'commission_amount', 'owner_share_amount', 'held_by_admin'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
