<?php

declare(strict_types=1);

namespace App\Http\Requests\Club;

use App\Enums\PlayTime;
use App\Enums\WorkingDays;
use App\Models\Court;
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

    /** @return array<string, array<int, ValidationRule|string>> */
    public function rules(): array
    {
        return [
            'court_id' => ['required', 'integer', 'exists:courts,id'],
            'rules' => ['required', 'array', 'min:1'],
            'rules.*.day' => ['nullable', Rule::enum(WorkingDays::class)],
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
            },
        ];
    }

    /**
     * @return array<int, array{
     *     day: string|null,
     *     items: array<int, array{
     *         play_time_minutes: int,
     *         prices: array<int, array{starts_at: string, price: int|float|string}>
     *     }>
     * }>
     */
    public function rulesPayload(): array
    {
        /** @var array<int, array{day: string|null, items: array<int, array{play_time_minutes: int, prices: array<int, array{starts_at: string, price: int|float|string}>}>}> $rules */
        $rules = $this->input('rules', []);

        return $rules;
    }
}
