<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username', 100)->nullable()->after('name');
            });
        }

        if (! Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone', 20)->nullable()->after('email');
            });
        }

        if (! Schema::hasColumn('users', 'profile_image')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('profile_image')->nullable()->after('phone');
            });
        }

        if (Schema::hasColumn('users', 'username')) {
            $users = DB::table('users')
                ->select('id', 'name', 'email', 'username')
                ->whereNull('username')
                ->get();

            foreach ($users as $user) {
                $source = $user->name ?: (explode('@', (string) $user->email)[0] ?? 'user');
                $base = preg_replace('/[^a-z0-9_]+/i', '_', strtolower(trim($source))) ?: 'user';
                $base = trim($base, '_');
                $base = $base === '' ? 'user' : $base;

                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'username' => substr($base, 0, 40) . '_' . $user->id,
                    ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'profile_image')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('profile_image');
            });
        }

        if (Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('username');
            });
        }
    }
};
