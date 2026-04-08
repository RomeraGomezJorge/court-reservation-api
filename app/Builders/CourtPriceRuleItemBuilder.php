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
     * Retrieves the unique and ordered play times for a court.
     *
     * @return array<int, int>
     */
    public function getPlayTimesForCourt(int|string $courtId): array
    {
        $courtPlayTimeEnums = $this
            ->whereRelation(relation: 'priceRule', column: 'court_id', value: $courtId)
            ->distinct('play_time_minutes')
            ->orderBy('play_time_minutes')
            ->pluck('play_time_minutes');

        return $courtPlayTimeEnums
            ->map(fn (PlayTime $value): int => $value->value)
            ->values()
            ->all();
    }
}
