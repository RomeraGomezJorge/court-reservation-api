<?php

declare(strict_types=1);

namespace App\Http\Requests\Club;

use App\Enums\CourtPriceRuleDay;
use App\Enums\PlayTime;
use App\Models\Court;
use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class StoreCourtPriceRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, ValidationRule|RuleContract|string>> */
    public function rules(): array
    {
        return [
            'court_id' => ['required', 'integer', 'exists:courts,id'],
            'rules' => ['required', 'array', 'min:1'],
            'rules.*.day' => ['required', 'distinct', Rule::enum(CourtPriceRuleDay::class)],
            'rules.*.items' => ['required', 'array', 'min:1'],
            'rules.*.items.*.play_time_minutes' => ['required', Rule::enum(PlayTime::class)],
            'rules.*.items.*.prices' => ['required', 'array', 'min:1'],
            'rules.*.items.*.prices.*.starts_at' => ['required', 'date_format:H:i'],
            'rules.*.items.*.prices.*.price' => ['required', 'numeric', 'min:0'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                /** @var Court $court */
                $court = $this->route('court');

                if ((string) $this->input('court_id') !== (string) $court->id) {
                    $validator->errors()->add('court_id', __('validation.court_id_must_match_route_court'));
                }

                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $this->validateNoDuplicateDayAndTimeWithItems($validator);
                $this->validateNoDuplicatePricesPerDay($validator);
                $this->validateNoOverlapsWithinSamePlayTime($validator);
            },
        ];
    }

    /**
     * @return array<int, array{
     *     day: string,
     *     items: array<int, array{
     *         play_time_minutes: int,
     *         prices: array<int, array{starts_at: string, price: int|float|string}>
     *     }>
     * }>
     */
    public function rulesPayload(): array
    {
        /** @var array<int, array{day: string, items: array<int, array{play_time_minutes: int, prices: array<int, array{starts_at: string, price: int|float|string}>}>}> $rules */
        $rules = $this->input('rules', []);

        return $rules;
    }

    private function validateNoDuplicateDayAndTimeWithItems(Validator $validator): void
    {
        /** @var array<string, true> $uniqueItemByDayAndTime */
        $uniqueItemByDayAndTime = [];

        foreach ($this->rulesPayload() as $ruleIndex => $rule) {
            foreach ($rule['items'] as $itemIndex => $item) {
                foreach ($item['prices'] as $priceIndex => $priceRow) {
                    $priceSlotIdentifier = $this->priceSlotKey(
                        $rule['day'],
                        $item['play_time_minutes'],
                        $priceRow['starts_at'],
                    );

                    if (isset($uniqueItemByDayAndTime[$priceSlotIdentifier])) {
                        $validator->errors()->add(
                            "rules.{$ruleIndex}.items.{$itemIndex}.prices.{$priceIndex}.starts_at",
                            __('validation.court_price_rule_duplicate_slot'),
                        );

                        continue;
                    }

                    $uniqueItemByDayAndTime[$priceSlotIdentifier] = true;
                }
            }
        }
    }

    private function validateNoDuplicatePricesPerDay(Validator $validator): void
    {
        /** @var array<string, array<string, int>> $priceOccurrencesByDay */
        $priceOccurrencesByDay = [];

        foreach ($this->rulesPayload() as $rule) {
            $day = $rule['day'];

            foreach ($rule['items'] as $item) {
                foreach ($item['prices'] as $priceRow) {
                    $normalizedPrice = $this->normalizedPrice($priceRow['price']);

                    if (! isset($priceOccurrencesByDay[$day][$normalizedPrice])) {
                        $priceOccurrencesByDay[$day][$normalizedPrice] = 0;
                    }

                    $priceOccurrencesByDay[$day][$normalizedPrice]++;
                }
            }
        }

        foreach ($priceOccurrencesByDay as $day => $priceOccurrences) {
            $duplicatedPrices = array_keys(
                array_filter(
                    $priceOccurrences,
                    fn (int $occurrences): bool => $occurrences > 1,
                ));

            if ($duplicatedPrices === []) {
                continue;
            }

            sort($duplicatedPrices, SORT_NUMERIC);

            $dayLabel = CourtPriceRuleDay::tryFrom($day)?->label() ?? $day;

            $validator->errors()->add(
                'rules',
                __('validation.court_price_rule_duplicate_price_per_day', [
                    'prices' => implode(', ', $duplicatedPrices),
                    'day' => $dayLabel,
                ]),
            );
        }
    }

    private function validateNoOverlapsWithinSamePlayTime(Validator $validator): void
    {
        foreach ($this->rulesPayload() as $ruleIndex => $rule) {
            foreach ($rule['items'] as $itemIndex => $item) {
                $playTimeMinutes = (int) $item['play_time_minutes'];
                $prices = $item['prices'];

                foreach ($prices as $currentIndex => $currentPrice) {
                    $slotStart = Date::createFromTimeString($currentPrice['starts_at']);
                    $slotEnd = $slotStart->copy()->addMinutes($playTimeMinutes);

                    foreach ($prices as $otherIndex => $otherPrice) {
                        if ($otherIndex <= $currentIndex) {
                            continue; // evitar comparar contra sí mismo o duplicar pares
                        }

                        $otherStart = Date::createFromTimeString($otherPrice['starts_at']);

                        $otherStartsBeforeCurrentEnds = $otherStart->isBefore($slotEnd);

                        if ($otherStartsBeforeCurrentEnds) {
                            $validator->errors()->add(
                                "rules.{$ruleIndex}.items.{$itemIndex}.prices.{$otherIndex}.starts_at",
                                __('validation.court_price_rule_overlap_within_play_time', [
                                    'start_time' => $otherPrice['starts_at'],
                                    'duration' => $playTimeMinutes,
                                    'conflict_start_time' => $currentPrice['starts_at'],
                                    'conflict_end_time' => $slotEnd->format('H:i'),
                                ]),
                            );
                        }
                    }
                }
            }
        }
    }

    private function priceSlotKey(string $day, int $playTimeMinutes, string $startsAt): string
    {
        return "{$day}|{$playTimeMinutes}|{$startsAt}";
    }

    private function normalizedPrice(int|float|string $price): string
    {
        return number_format((float) $price, 2, '.', '');
    }
}
