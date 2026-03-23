<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120);
            $table->text('message');
            $table->string('location', 80)->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index(['is_visible', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_reviews');
    }
};
