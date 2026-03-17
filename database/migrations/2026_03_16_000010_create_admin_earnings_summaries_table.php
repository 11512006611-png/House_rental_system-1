<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_earnings_summaries', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_commission_earned', 12, 2)->default(0);
            $table->timestamp('last_transaction_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_earnings_summaries');
    }
};
