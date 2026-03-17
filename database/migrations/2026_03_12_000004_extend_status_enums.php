<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Extend users status to include 'suspended'
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('pending','approved','rejected','suspended') NOT NULL DEFAULT 'approved'");
        // Extend houses status to include 'rejected'
        DB::statement("ALTER TABLE houses MODIFY COLUMN status ENUM('available','rented','pending','rejected') NOT NULL DEFAULT 'available'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'approved'");
        DB::statement("ALTER TABLE houses MODIFY COLUMN status ENUM('available','rented','pending') NOT NULL DEFAULT 'available'");
    }
};
