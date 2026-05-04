<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            $table->unsignedBigInteger('inspected_by_admin_id')->nullable()->after('owner_id');
            $table->timestamp('inspected_at')->nullable()->after('status');
            $table->text('admin_inspection_notes')->nullable()->after('inspected_at');

            $table->foreign('inspected_by_admin_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            $table->dropForeign(['inspected_by_admin_id']);
            $table->dropColumn([
                'inspected_by_admin_id',
                'inspected_at',
                'admin_inspection_notes',
            ]);
        });
    }
};
