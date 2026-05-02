<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CourtPriceRuleDay;
use App\Enums\PlayTime;
use App\Models\Court;
use App\Models\CourtPriceRule;
use App\Models\CourtPriceRuleItem;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

final class CourtPriceRulesShowBuilderService
{
    /**
     * @return array{
     *     court_id: int|string,
     *     play_time: array<int, int>,
     *     price_starts_at: array<int, string>,
     *     days: array<int, array{
     *         day: string,
     *         label: string,
     *         time_slots: array<int, array{
     *             label: string,
     *             starts_at: string,
     *             prices: array<string, string|null>
     *         }>
     *     }>
     * }
     */
    public function handle(Court $court): array
    {
        /** @var Collection<int, CourtPriceRule> $priceRules */
        $priceRules = $court->priceRules()->with('items')->get();

        $priceStartsAt = CourtPriceRuleItem::query()->getPriceStartsAtForCourt($court->id);
        $playTime = CourtPriceRuleItem::query()->getPlayTimesForCourt($court->id);

        $priceStartsAt = array_map(
            fn (CarbonImmutable $time): string => $time->format('H:i'),
            $priceStartsAt,
        );

        return [
            'court_id' => $court->id,
            'play_time_minutes' => $playTime,
            'price_starts_at' => $priceStartsAt,
            'days' => $this->buildDays($priceRules, $playTime),
        ];
    }

    /**
     * @param  Collection<int, CourtPriceRule>  $priceRules
     * @param  array<int, int>  $playTime
     * @return array<int, array{day: string, label: string, time_slots: array<int, array{label: string, starts_at: string, prices: array<string, string|null>}>}>
     */
    private function buildDays(Collection $priceRules, array $playTime): array
    {
        return collect(CourtPriceRuleDay::cases())
            ->map(function (CourtPriceRuleDay $ruleDay) use ($priceRules, $playTime): array {
                $priceRule = $this->findPriceRuleForDay($priceRules, $ruleDay);

                return [
                    'day' => $ruleDay->value,
                    'label' => $ruleDay->label(),
                    'time_slots' => $priceRule instanceof CourtPriceRule
                        ? $this->getTimeSlots($priceRule, $playTime)
                        : [],
                ];
            })
            ->all();
    }

    /**
     * @param  Collection<int, CourtPriceRule>  $priceRules
     */
    private function findPriceRuleForDay(Collection $priceRules, CourtPriceRuleDay $targetDay): ?CourtPriceRule
    {
        return $priceRules->first(
            fn (CourtPriceRule $priceRule): bool => $priceRule->day === $targetDay,
        );
    }

    /**
     * @param  array<int, int>  $playTimes
     * @return array<int, array{label: string, starts_at: string, prices: array<string, string|null>}>
     */
    private function getTimeSlots(CourtPriceRule $priceRule, array $playTimes): array
    {
        $itemsGroupedByStartTime = [];

        foreach ($priceRule->items as $item) {
            $startTime = $item->price_starts_at->format('H:i');
            $itemsGroupedByStartTime[$startTime][] = $item;
        }

        ksort($itemsGroupedByStartTime);

        $timeSlots = [];

        foreach ($itemsGroupedByStartTime as $startTime => $items) {
            $timeSlots[] = [
                'label' => __('court-price-rules.slot_from', ['time' => $startTime]),
                'starts_at' => $startTime,
                'prices' => $this->getPlayTimePrices($items, $playTimes),
            ];
        }

        return $timeSlots;
    }

    /**
     * @param  array<int, CourtPriceRuleItem>  $priceRuleItems
     * @param  array<int, int>  $playTimes
     * @return array<string, string|null>
     */
    private function getPlayTimePrices(array $priceRuleItems, array $playTimes): array
    {
        $priceByDuration = [];

        foreach ($priceRuleItems as $priceRuleItem) {
            $durationInMinutes = $priceRuleItem->play_time_minutes->value;
            $priceByDuration[$durationInMinutes] = $priceRuleItem->price;
        }

        $playTimePrices = [];

        foreach ($playTimes as $durationInMinutes) {
            $playTimeLabel = PlayTime::from($durationInMinutes)->label();

            $playTimePrices[$playTimeLabel] = $priceByDuration[$durationInMinutes] ?? null;
        }

        return $playTimePrices;
    }
}
