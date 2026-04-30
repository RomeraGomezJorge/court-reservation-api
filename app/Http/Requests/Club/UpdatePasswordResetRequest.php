<?php

declare(strict_types=1);

namespace App\Http\Requests\Club;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class UpdatePasswordResetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, ValidationRule|string|Password>> */
    public function rules(): array
    {
        return [
            'token' => ['required'],
            'email' => [
                'required',
                'email',
                'string',
                'exists:club_users,email',
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::default(),
            ],
        ];
    }
}
