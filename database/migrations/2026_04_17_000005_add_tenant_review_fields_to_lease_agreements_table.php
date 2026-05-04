<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lease_agreements', function (Blueprint $table) {
            $table->unsignedSmallInteger('duration_months')->nullable()->after('security_deposit_amount');
            $table->enum('tenant_review_status', ['pending', 'accepted', 'rejected'])->default('pending')->after('payment_status');
            $table->timestamp('tenant_reviewed_at')->nullable()->after('tenant_review_status');
            $table->text('tenant_review_note')->nullable()->after('tenant_reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('lease_agreements', function (Blueprint $table) {
            $table->dropColumn([
                'duration_months',
                'tenant_review_status',
                'tenant_reviewed_at',
                'tenant_review_note',
            ]);
        });
    }
};
