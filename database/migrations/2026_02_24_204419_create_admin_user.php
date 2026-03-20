<?php

declare(strict_types=1);

use Database\Helpers\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function shouldRun(): bool
    {
        return MigrationHelper::shouldRunInTesting();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => 'admin9@admin.com',
            'password' => Hash::make('Admin123456!'),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->where('name', 'Admin')->delete();
    }
};
