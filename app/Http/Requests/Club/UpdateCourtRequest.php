<?php

declare(strict_types=1);

namespace App\Http\Requests\Club;

use App\Models\Club;
use App\Models\Court;
use Illuminate\Contracts\Validation\Rule as ValidationRuleContract;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

final class UpdateCourtRequest extends FormRequest
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

        /** @var Court $court */
        $court = $this->route('court');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('courts', 'name')
                    ->where(fn ($query) => $query->where('club_id', $club->id))
                    ->ignore($court->id),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'sport_type_id' => ['required', 'integer', 'exists:sport_types,id'],
            'features' => ['nullable', 'array'],
            'features.*' => ['integer', 'distinct', 'exists:features,id'],
        ];
    }

    /** @return array<string, mixed> */
    public function courtData(): array
    {
        return $this->safe()->except([
            'features',
        ]);
    }

    /** @return array<int, int> */
    public function featureIds(): array
    {
        $features = $this->input('features', []);

        return is_array($features)
            ? $features
            : [];
    }
}
