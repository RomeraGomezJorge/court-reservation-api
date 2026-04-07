<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ClubServicesType;
use App\Models\Club;
use App\Models\ClubService;
use Illuminate\Database\Seeder;

final class ClubServiceSeeder extends Seeder
{
    public function run(): void
    {
        $club = Club::query()->where('organization_name', 'Club Padel Admin Demo')->firstOrFail();

        foreach ($this->serviceTypes() as $serviceType) {
            ClubService::query()->updateOrCreate(
                [
                    'club_id' => $club->id,
                    'type' => $serviceType->value,
                ],
            );
        }
    }

    /**
     * @return array<int, ClubServicesType>
     */
    private function serviceTypes(): array
    {
        return [
            ClubServicesType::Wifi,
            ClubServicesType::Parking,
            ClubServicesType::Shower,
            ClubServicesType::Terrace,
            ClubServicesType::FirstAid,
        ];
    }
}

