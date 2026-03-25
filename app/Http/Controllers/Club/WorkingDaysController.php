<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Enums\WorkingDays;
use Illuminate\Http\JsonResponse;

final class WorkingDaysController
{
    public function __invoke(): JsonResponse
    {
        return response()->json(
            collect(WorkingDays::cases())
                ->map(fn (WorkingDays $workingDay): array => [
                    'value' => $workingDay->value,
                    'label' => $workingDay->label(),
                ])
                ->values()
                ->all(),
        );
    }
}
