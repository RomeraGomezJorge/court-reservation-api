<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_working_days', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->string('day');
            $table->time('opening_hour');
            $table->time('closing_hour');
            $table->timestamps();

            $table->index('club_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_working_days');
    }
};
