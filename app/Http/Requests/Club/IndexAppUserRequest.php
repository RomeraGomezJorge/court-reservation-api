<?php

declare(strict_types=1);

namespace App\Http\Requests\Club;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

final class IndexAppUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, ValidationRule|string|In>> */
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'string', 'max:100'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'sort_column' => [
                'nullable',
                'string',
                Rule::in([
                    'name',
                    'last_name',
                    'email',
                    'phone_number',
                ]),
            ],
            'sort_direction' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
            'per_page' => ['integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'min:1'],
        ];
    }
}
