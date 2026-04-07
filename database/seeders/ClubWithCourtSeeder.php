<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Club;
use App\Models\ClubUser;
use App\Models\Court;
use App\Models\Feature;
use App\Models\SportType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class ClubWithCourtSeeder extends Seeder
{
    public function run(): void
    {
        $clubUser = $this->upsertClubUser();
        $club = $this->upsertClub($clubUser->id);
        $padel = SportType::query()->where('name', 'Padel')->firstOrFail();

        $court = Court::query()->updateOrCreate(
            [
                'club_id' => $club->id,
                'name' => 'Cancha Padel Central',
            ],
            [
                'sport_type_id' => $padel->id,
                'description' => 'Cancha de padel para datos de administracion',
                'is_available' => true,
            ],
        );

        $court->features()->sync(
            Feature::query()->whereIn('name', ['Iluminacion', 'Techada'])->pluck('id')->all(),
        );
    }

    private function upsertClubUser(): ClubUser
    {
        /** @var ClubUser $clubUser */
        $clubUser = ClubUser::query()->updateOrCreate(
            ['email' => 'club.demo@example.com'],
            [
                'password' => Hash::make('ClubDemo12345!'),
                'email_verified_at' => now(),
            ],
        );

        return $clubUser;
    }

    private function upsertClub(int|string $clubUserId): Club
    {
        /** @var Club $club */
        $club = Club::query()->updateOrCreate(
            ['organization_name' => 'Club Padel Admin Demo'],
            [
                'club_user_id' => $clubUserId,
                'address_city' => 'Santiago',
                'address_country' => 'Chile',
                'address_postal_code' => '8320000',
                'address_state' => 'Region Metropolitana',
                'address_street' => 'Av. Providencia 1000',
                'description' => 'Club de referencia para endpoints de administracion',
                'facebook_url' => 'https://facebook.com/club.padel.admin.demo',
                'instagram_url' => 'https://instagram.com/club.padel.admin.demo',
                'latitude' => '-33.4489',
                'longitude' => '-70.6693',
                'operating_hours_additional_info' => 'Lunes a domingo de 09:00 a 23:00',
                'phone_number' => '+56912345678',
                'reservation_policies_and_payment_terms' => 'Pago anticipado del 50%',
                'twitter_url' => 'https://x.com/club.padel.admin.demo',
                'whatsapp_number' => '+56912345678',
                'is_active' => true,
            ],
        );

        return $club;
    }
}
