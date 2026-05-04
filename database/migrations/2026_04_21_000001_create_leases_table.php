<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained('rentals')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('house_id')->constrained('houses')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('original_name');
            $table->decimal('monthly_rent', 12, 2)->nullable();
            $table->decimal('deposit_amount', 12, 2)->nullable();
            $table->decimal('security_deposit_amount', 12, 2)->nullable();
            $table->unsignedInteger('duration_months')->nullable();
            $table->string('status')->default('sent');
            $table->string('payment_status')->default('pending');
            $table->string('tenant_review_status')->default('pending');
            $table->timestamp('tenant_reviewed_at')->nullable();
            $table->text('tenant_review_note')->nullable();
            $table->date('lease_start_date')->nullable();
            $table->date('lease_end_date')->nullable();
            $table->string('tenant_signature_name')->nullable();
            $table->timestamp('tenant_signed_at')->nullable();
            $table->string('owner_signature_name')->nullable();
            $table->timestamp('owner_signed_at')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique('rental_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leases');
    }
};