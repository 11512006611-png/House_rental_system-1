<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('service_fee_rate', 5, 2)->default(0)->after('security_deposit_amount');
            $table->decimal('service_fee_amount', 10, 2)->default(0)->after('service_fee_rate');
            $table->decimal('total_advance_amount', 10, 2)->default(0)->after('service_fee_amount');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('rent_amount', 10, 2)->default(0)->after('amount');
            $table->decimal('security_deposit_amount', 10, 2)->default(0)->after('rent_amount');
            $table->decimal('service_fee_rate', 5, 2)->default(0)->after('security_deposit_amount');
            $table->decimal('service_fee_amount', 10, 2)->default(0)->after('service_fee_rate');
            $table->decimal('total_advance_amount', 10, 2)->default(0)->after('service_fee_amount');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'rent_amount',
                'security_deposit_amount',
                'service_fee_rate',
                'service_fee_amount',
                'total_advance_amount',
            ]);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'service_fee_rate',
                'service_fee_amount',
                'total_advance_amount',
            ]);
        });
    }
};
