<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Enums\ClubServicesType;
use Illuminate\Http\JsonResponse;

final class ClubServiceTypesController
{
    public function __invoke(): JsonResponse
    {
        return response()->json(
            collect(ClubServicesType::cases())
                ->map(fn (ClubServicesType $clubServiceType): array => [
                    'value' => $clubServiceType->value,
                    'label' => $clubServiceType->label(),
                    'icon' => $clubServiceType->getIcon(),
                ])
                ->values()
                ->all(),
        );
    }
}
