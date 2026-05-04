<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('booking_id')->nullable()->after('rental_id')->constrained('bookings')->nullOnDelete();
            $table->enum('payment_type', ['first_month_rent', 'monthly_rent', 'security_deposit', 'refund'])->default('monthly_rent')->after('payment_date');
            $table->boolean('held_by_admin')->default(false)->after('payment_type');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('booking_id');
            $table->dropColumn(['payment_type', 'held_by_admin']);
        });
    }
};