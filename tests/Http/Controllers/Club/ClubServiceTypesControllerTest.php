<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Club;

use App\Enums\ClubServicesType;
use App\Http\Controllers\Club\ClubServiceTypesController;

use function Pest\Laravel\get;

beforeEach(function (): void {
    actingAsClubUser();
});

it('returns a collection of club service types with value label and icon', function (): void {
    get(action([ClubServiceTypesController::class]))
        ->assertOk()
        ->assertExactJson(
            collect(ClubServicesType::cases())
                ->map(fn (ClubServicesType $clubServiceType): array => [
                    'value' => $clubServiceType->value,
                    'label' => $clubServiceType->label(),
                    'icon' => $clubServiceType->getIcon(),
                ])
                ->values()
                ->all(),
        );
});
