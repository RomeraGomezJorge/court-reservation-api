<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table): void {
            // Add foreign key to club_users
            $table->foreignId('club_user_id')->nullable()->after('id')->constrained('club_users')->onDelete('cascade');

            // Remove authentication fields
            $table->dropColumn(['email', 'password', 'email_verified_at']);
        });
    }

    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table): void {
            // Restore authentication fields
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Remove foreign key
            $table->dropForeign(['club_user_id']);
            $table->dropColumn('club_user_id');
        });
    }
};
