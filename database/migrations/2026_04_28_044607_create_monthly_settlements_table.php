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
        Schema::create('monthly_settlements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id');
            $table->date('settlement_month'); // e.g., 2026-04-01 for April 2026
            $table->decimal('total_rent_collected', 12, 2)->default(0);
            $table->decimal('commission_rate', 5, 2)->default(10.00); // 10% default
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('final_amount', 12, 2)->default(0);
            $table->enum('status', ['pending', 'transferred', 'cancelled'])->default('pending');
            $table->date('transferred_at')->nullable();
            $table->text('transfer_notes')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable(); // admin who processed
            $table->json('payment_breakdown')->nullable(); // detailed breakdown of payments included
            $table->timestamps();

            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');

            $table->unique(['owner_id', 'settlement_month']); // One settlement per owner per month
            $table->index(['settlement_month', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_settlements');
    }
};
