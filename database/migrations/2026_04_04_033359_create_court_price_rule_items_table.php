<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('court_price_rule_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('court_price_rule_id')->constrained('court_price_rules')->cascadeOnDelete();
            $table->unsignedInteger('play_time_minutes');
            $table->decimal('price', 10, 2);
            $table->time('price_starts_at');
            $table->timestamps();

            $table->unique(['court_price_rule_id', 'play_time_minutes', 'price_starts_at'], 'cpri_rule_time_start_uq');
            $table->index('court_price_rule_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('court_price_rule_items');
    }
};
