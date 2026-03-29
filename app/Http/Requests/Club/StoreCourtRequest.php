<?php

declare(strict_types=1);

namespace App\Http\Requests\Club;

use App\Models\Club;
use Illuminate\Contracts\Validation\Rule as ValidationRuleContract;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreCourtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, ValidationRule|ValidationRuleContract|string>> */
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
                    fn($query) => $query->where('club_id', $club->id),
                ),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'sport_type_id' => ['required', 'integer', 'exists:sport_types,id'],
            'features' => ['nullable', 'array'],
            'features.*' => ['integer', 'distinct', 'exists:features,id'],
        ];
    }

    /** @return array{name: string, description: string|null, sport_type_id: int} */
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
