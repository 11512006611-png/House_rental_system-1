<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_commission_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->constrained('houses')->cascadeOnDelete();
            $table->decimal('payment_amount', 10, 2);
            $table->decimal('admin_commission', 10, 2);
            $table->decimal('owner_share', 10, 2);
            $table->date('transaction_date');
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_commission_transactions');
    }
};
