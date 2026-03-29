<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\SportType;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreSportTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<ValidationRule|string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'unique:sport_types,name', 'max:255'],
        ];
    }
}
