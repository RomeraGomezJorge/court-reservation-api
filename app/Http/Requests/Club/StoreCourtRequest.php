<?php

declare(strict_types=1);

namespace App\Http\Requests\Club;

use App\Models\Club;
use Illuminate\Contracts\Validation\Rule as ValidationRuleContract;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

final class StoreCourtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, ValidationRule|ValidationRuleContract|Unique|string>> */
    public function rules(): array
    {
        /** @var Club $club */
        $club = $this->route('club');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('courts', 'name')->where(
                    fn (Builder $query): Builder => $query->where('club_id', $club->id),
                ),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'sport_type_id' => ['required', 'integer', 'exists:sport_types,id'],
            'feature_ids' => ['nullable', 'array'],
            'feature_ids.*' => ['integer', 'distinct', 'exists:features,id'],
        ];
    }

    /** @return array<string, mixed> */
    public function courtData(): array
    {
        return $this->safe()->except([
            'feature_ids',
        ]);
    }

    /** @return array<int, int> */
    public function featureIds(): array
    {
        $featureIds = $this->input('feature_ids', []);

        return is_array($featureIds)
            ? $featureIds
            : [];
    }
}
