<?php

declare(strict_types=1);

namespace App\Builders;

use App\Enums\PlayTime;
use App\Models\CourtPriceRuleItem;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends Builder<CourtPriceRuleItem>
 */
final class CourtPriceRuleItemBuilder extends Builder
{
    /**
     * Obtiene los tiempos de juego únicos y ordenados para una cancha.
     *
     * @return array<int, int>
     */
    public function getPlayTimesForCourt(int|string $courtId): array
    {
        $playTimeEnums = $this
            ->whereHas('priceRule', function (Builder $query) use ($courtId): void {
                $query->where('court_id', $courtId);
            })
            ->distinct('play_time_minutes')
            ->orderBy('play_time_minutes')
            ->pluck('play_time_minutes');

        return $playTimeEnums
            ->map(fn (PlayTime $value): int => $value->value)
            ->values()
            ->all();
    }
}
