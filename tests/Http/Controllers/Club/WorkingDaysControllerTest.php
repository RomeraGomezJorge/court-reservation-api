<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Club;

use App\Enums\WorkingDays;
use App\Http\Controllers\Club\WorkingDaysController;

use function Pest\Laravel\get;

beforeEach(function (): void {
    actingAsClubUser();
});

it('returns a collection of working days with value and label', function (): void {
    get(action([WorkingDaysController::class]))
        ->assertOk()
        ->assertExactJson(
            collect(WorkingDays::cases())
                ->map(fn (WorkingDays $workingDay): array => [
                    'value' => $workingDay->value,
                    'label' => $workingDay->label(),
                ])
                ->values()
                ->all(),
        );
});
