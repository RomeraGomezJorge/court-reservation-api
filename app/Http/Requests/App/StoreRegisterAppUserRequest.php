<?php

declare(strict_types=1);

namespace App\Http\Requests\App;

use App\Enums\Gender;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

final class StoreRegisterAppUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, ValidationRule|Rule|Enum|string|Password>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone_number' => ['required', 'string', 'max:50', 'unique:app_users,phone_number'],
            'email' => ['email', 'max:100', 'unique:app_users,email'],
            'birthday' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::default(),
            ],
        ];
    }
}
