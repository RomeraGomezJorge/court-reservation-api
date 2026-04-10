<?php

declare(strict_types=1);

namespace App\Builders;

use App\Enums\PlayTime;
use App\Models\CourtPriceRuleItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;

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
    public function getPlayTimesForCourt(int $courtId): array
    {
        $courtPlayTimeEnums = $this
            ->whereRelation('priceRule', 'court_id', $courtId)
            ->distinct('play_time_minutes')
            ->orderBy('play_time_minutes')
            ->pluck('play_time_minutes');

        return $courtPlayTimeEnums
            ->map(fn (PlayTime $value): int => $value->value)
            ->values()
            ->all();
    }

    /**
     * Retrieves unique and sorted price start times for a court.
     *
     * @return array<int, string>
     */
    public function getPriceStartsAtForCourt(int $courtId): array
    {
        $priceStartTimes = $this
            ->whereRelation('priceRule', 'court_id', $courtId)
            ->distinct('price_starts_at')
            ->oldest('price_starts_at')
            ->pluck('price_starts_at');

        return $priceStartTimes
            ->map(fn (string $time): string => $this->formatTime($time))
            ->values()
            ->all();
    }

    /**
     * Formats time from H:i:s to H:i format.
     *
     * @param  string  $time  Time in H:i:s format
     * @return string Formatted time in H:i format
     */
    private function formatTime(string $time): string
    {
        $parsedTime = Date::createFromFormat('H:i:s', $time);

        return $parsedTime?->format('H:i') ?? $time;
    }
}
