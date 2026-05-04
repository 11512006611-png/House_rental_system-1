<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lease_agreements', function (Blueprint $table) {
            $table->foreignId('booking_id')->nullable()->after('rental_id')->constrained('bookings')->nullOnDelete();
            $table->decimal('security_deposit_amount', 10, 2)->default(0)->after('deposit_amount');
        });
    }

    public function down(): void
    {
        Schema::table('lease_agreements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('booking_id');
            $table->dropColumn('security_deposit_amount');
        });
    }
};