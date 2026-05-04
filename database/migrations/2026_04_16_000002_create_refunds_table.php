<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('move_out_request_id')->nullable()->constrained('move_out_requests')->nullOnDelete();
            $table->foreignId('house_id')->constrained('houses')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('processed_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('security_deposit_amount', 10, 2)->default(0);
            $table->decimal('damage_cost', 10, 2)->default(0);
            $table->decimal('pending_dues', 10, 2)->default(0);
            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->enum('status', ['draft', 'pending', 'approved', 'processed', 'rejected'])->default('draft');
            $table->text('inspection_notes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};