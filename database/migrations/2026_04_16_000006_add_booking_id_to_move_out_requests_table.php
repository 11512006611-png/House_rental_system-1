<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('move_out_requests', function (Blueprint $table) {
            $table->foreignId('booking_id')->nullable()->after('house_id')->constrained('bookings')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('move_out_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('booking_id');
        });
    }
};