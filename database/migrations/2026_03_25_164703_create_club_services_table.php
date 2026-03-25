<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('club_services', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->string('type');
            $table->timestamps();

            $table->unique(['club_id', 'type']);
            $table->index('club_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('club_services');
    }
};
