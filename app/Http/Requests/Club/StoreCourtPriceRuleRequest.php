<?php

declare(strict_types=1);

namespace App\Http\Requests\Club;

use App\Enums\CourtPriceRuleDay;
use App\Enums\PlayTime;
use App\Models\Court;
use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
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
            'rules.*.items.*.prices.*.starts_at' => ['required', 'date_format:H:i:s'],
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
                $this->validateNoDuplicatePricesWithinItems($validator);
                $this->validateNoDuplicatePricesPerDay($validator);
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
                        $priceRow['starts_at']
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

    private function validateNoDuplicatePricesWithinItems(Validator $validator): void
    {
        foreach ($this->rulesPayload() as $ruleIndex => $rule) {
            foreach ($rule['items'] as $itemIndex => $item) {

                /** @var array<string, true> $uniquePricesPerItem */
                $uniquePricesPerItem = [];

                foreach ($item['prices'] as $priceIndex => $priceRow) {
                    $price = (string) $priceRow['price'];

                    if (isset($uniquePricesPerItem[$price])) {
                        $validator->errors()->add(
                            "rules.{$ruleIndex}.items.{$itemIndex}.prices.{$priceIndex}.price",
                            __('validation.court_price_rule_duplicate_price'),
                        );

                        continue;
                    }

                    $uniquePricesPerItem[$price] = true;
                }
            }
        }
    }

    private function validateNoDuplicatePricesPerDay(Validator $validator): void
    {
        $priceUsedBy = []; // day|price → "ruleIndex|itemIndex"

        foreach ($this->rulesPayload() as $ruleIndex => $rule) {
            foreach ($rule['items'] as $itemIndex => $item) {
                foreach ($item['prices'] as $priceIndex => $priceRow) {
                    $key = $this->dayPriceKey($rule['day'], $this->normalizedPrice($priceRow['price']));
                    $currentItem = $this->itemKey($ruleIndex, $itemIndex);

                    $alreadyUsedByAnotherItem = isset($priceUsedBy[$key]) && $priceUsedBy[$key] !== $currentItem;

                    if ($alreadyUsedByAnotherItem) {
                        $validator->errors()->add(
                            "rules.{$ruleIndex}.items.{$itemIndex}.prices.{$priceIndex}.price",
                            __('validation.court_price_rule_duplicate_price_per_day'),
                        );

                        continue;
                    }

                    $priceUsedBy[$key] = $currentItem;
                }
            }
        }
    }

    private function priceSlotKey(string $day, int $playTimeMinutes, string $startsAt): string
    {
        return "{$day}|{$playTimeMinutes}|{$startsAt}";
    }

    private function dayPriceKey(string $day, string $price): string
    {
        return "{$day}|{$price}";
    }

    private function itemKey(int $ruleIndex, int $itemIndex): string
    {
        return "{$ruleIndex}|{$itemIndex}";
    }

    private function normalizedPrice(int|float|string $price): string
    {
        return number_format((float) $price, 2, '.', '');
    }
}
