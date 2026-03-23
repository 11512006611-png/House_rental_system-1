<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lease_agreements', function (Blueprint $table) {
            $table->string('agreement_id')->nullable()->unique()->after('id');
            $table->foreignId('tenant_id')->nullable()->after('owner_id')->constrained('users')->nullOnDelete();
            $table->foreignId('house_id')->nullable()->after('tenant_id')->constrained('houses')->nullOnDelete();
            $table->decimal('monthly_rent', 10, 2)->default(0)->after('original_name');
            $table->decimal('deposit_amount', 10, 2)->default(0)->after('monthly_rent');
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending')->after('deposit_amount');
            $table->date('lease_start_date')->nullable()->after('payment_status');
            $table->date('lease_end_date')->nullable()->after('lease_start_date');
            $table->string('tenant_signature_name')->nullable()->after('lease_end_date');
            $table->timestamp('tenant_signed_at')->nullable()->after('tenant_signature_name');
            $table->string('owner_signature_name')->nullable()->after('tenant_signed_at');
            $table->timestamp('owner_signed_at')->nullable()->after('owner_signature_name');
            $table->timestamp('generated_at')->nullable()->after('uploaded_at');
        });
    }

    public function down(): void
    {
        Schema::table('lease_agreements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropConstrainedForeignId('house_id');
            $table->dropUnique(['agreement_id']);
            $table->dropColumn([
                'agreement_id',
                'monthly_rent',
                'deposit_amount',
                'payment_status',
                'lease_start_date',
                'lease_end_date',
                'tenant_signature_name',
                'tenant_signed_at',
                'owner_signature_name',
                'owner_signed_at',
                'generated_at',
            ]);
        });
    }
};
