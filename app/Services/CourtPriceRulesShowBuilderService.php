<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\WorkingDays;
use App\Models\Court;
use App\Models\CourtPriceRule;
use App\Models\CourtPriceRuleItem;
use Date;
use Illuminate\Database\Eloquent\Collection;

final class CourtPriceRulesShowBuilderService
{
    /**
     * @return array{
     *     court_id: int|string,
     *     play_time: array<int, int>,
     *     price_starts_at: array<int, string>,
     *     days: array<int, array{
     *         day: string|null,
     *         label: string,
     *         time_slots: array<int, array{
     *             label: string,
     *             starts_at: string,
     *             prices: array<int|string, int|float|null>
     *         }>
     *     }>
     * }
     */
    public function handle(Court $court): array
    {
        /** @var Collection<int, CourtPriceRule> $priceRules */
        $priceRules = $court->priceRules()->with('items')->get();

        $playTime = CourtPriceRuleItem::query()->getPlayTimesForCourt($court->id);

        info('playtime', [$playTime]);

        return [
            'court_id' => $court->id,
            'play_time' => $playTime,
            'price_starts_at' => $this->collectPriceStartsAtValues($priceRules),
            'days' => $this->buildDays($priceRules, $playTime),
        ];
    }

    /**
     * @param  Collection<int, CourtPriceRule>  $priceRules
     * @return array<int, string>
     */
    private function collectPriceStartsAtValues(Collection $priceRules): array
    {
        return $priceRules
            ->flatMap(fn (CourtPriceRule $priceRule): Collection => $priceRule->items)
            ->map(fn (CourtPriceRuleItem $item): string => $this->formatTime($item->price_starts_at))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, CourtPriceRule>  $priceRules
     * @param  array<int, int>  $playTime
     * @return array<int, array{day: string|null, label: string, time_slots: array<int, array{label: string, starts_at: string, prices: array<int|string, int|float|null>}>}>
     */
    private function buildDays(Collection $priceRules, array $playTime): array
    {
        $orderedDays = [
            null,
            WorkingDays::Monday,
            WorkingDays::Tuesday,
            WorkingDays::Wednesday,
            WorkingDays::Thursday,
            WorkingDays::Friday,
            WorkingDays::Saturday,
            WorkingDays::Sunday,
            WorkingDays::Holiday,
        ];

        return collect($orderedDays)
            ->map(function (?WorkingDays $day) use ($priceRules, $playTime): array {
                $priceRule = $this->findPriceRuleForDay($priceRules, $day);

                return [
                    'day' => $day?->value,
                    'label' => $day?->label() ?? __('court-price-rules.generic_day'),
                    'time_slots' => $priceRule instanceof CourtPriceRule
                        ? $this->buildTimeSlots($priceRule, $playTime)
                        : [],
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, CourtPriceRule>  $priceRules
     */
    private function findPriceRuleForDay(Collection $priceRules, ?WorkingDays $day): ?CourtPriceRule
    {
        return $priceRules->first(
            fn (CourtPriceRule $priceRule): bool => $priceRule->day?->value === $day?->value,
        );
    }

    /**
     * @param  array<int, int>  $playTime
     * @return array<int, array{label: string, starts_at: string, prices: array<int|string, int|float|null>}>
     */
    private function buildTimeSlots(CourtPriceRule $priceRule, array $playTime): array
    {
        return $priceRule->items
            ->groupBy(fn (CourtPriceRuleItem $item): string => $this->formatTime($item->price_starts_at))
            ->sortKeys()
            ->map(fn (Collection $items, string $startsAt): array => [
                'label' => __('court-price-rules.slot_from', ['time' => $startsAt]),
                'starts_at' => $startsAt,
                'prices' => $this->buildPricesByPlayTime($items, $playTime),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, CourtPriceRuleItem>  $items
     * @param  array<int, int>  $playTime
     * @return array<int|string, int|float|null>
     */
    private function buildPricesByPlayTime(Collection $items, array $playTime): array
    {
        $pricesByPlayTime = $items
            ->keyBy(fn (CourtPriceRuleItem $item): int => $item->play_time_minutes->value)
            ->map(fn (CourtPriceRuleItem $item): int|float => $this->normalizePrice($item->price));

        /** @var array<int|string, int|float|null> $prices */
        $prices = [];

        foreach ($playTime as $minutes) {
            $prices[(string) $minutes] = $pricesByPlayTime->get($minutes);
        }

        return $prices;
    }

    private function formatTime(string $time): string
    {
        $parsedTime = Date::createFromFormat('H:i:s', $time);

        return $parsedTime?->format('H:i') ?? $time;
    }

    private function normalizePrice(int|float|string $price): int|float
    {
        $numericPrice = (float) $price;

        if ((float) ((int) $numericPrice) === $numericPrice) {
            return (int) $numericPrice;
        }

        return round($numericPrice, 2);
    }
}
