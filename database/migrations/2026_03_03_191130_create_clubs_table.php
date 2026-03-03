<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('address_city');
            $table->string('address_country');
            $table->string('address_postal_code');
            $table->string('address_state');
            $table->string('address_street');
            $table->string('description');
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('latitude');
            $table->string('longitude');
            $table->string('operating_hours_additional_info')->nullable();
            $table->string('organization_name')->unique();
            $table->string('phone_number')->nullable();
            $table->string('reservation_policies_and_payment_terms');
            $table->string('twitter_url')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};
