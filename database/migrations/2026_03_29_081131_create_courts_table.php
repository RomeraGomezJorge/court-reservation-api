<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('sport_type_id')->constrained('sport_types');
            $table->string('name', 100);
            $table->string('description', 255)->nullable();
            $table->boolean('is_available');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['club_id', 'name']);
            $table->index('club_id');
            $table->index('sport_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courts');
    }
};
