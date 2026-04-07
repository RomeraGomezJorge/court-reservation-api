<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\WorkingDays;
use App\Models\Club;
use App\Models\ClubWorkingDay;
use Illuminate\Database\Seeder;

final class ClubWorkingDaySeeder extends Seeder
{
    public function run(): void
    {
        $club = Club::query()->where('organization_name', 'Club Padel Admin Demo')->firstOrFail();

        foreach (WorkingDays::cases() as $day) {
            ClubWorkingDay::query()->updateOrCreate(
                [
                    'club_id' => $club->id,
                    'day' => $day->value,
                ],
                [
                    'opening_hour' => '09:00:00',
                    'closing_hour' => '23:00:00',
                ],
            );
        }
    }
}

