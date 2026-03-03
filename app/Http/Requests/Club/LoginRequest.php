<?php

declare(strict_types=1);

namespace App\Http\Requests\Club;

use App\Models\Club;
use App\Models\User;
use Hash;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * @property string $email
 * @property string $password
 */
final class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<ValidationRule|string>> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'password' => [
                'string',
                'required',
                'min:12',
                'max:255',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {

                if ($validator->errors()->count() > 0) {
                    return;
                }

                $club = Club::query()->where('email', $this->email)->first();

                if ($club === null || ! Hash::check($this->password, $club->password)) {
                    $validator->errors()->add(
                        'password',
                        __('auth.failed')
                    );

                    return;
                }

                if (! $club->hasVerifiedEmail()) {
                    $validator->errors()->add(
                        'email',
                        __('auth.email_not_verified')
                    );
                }
            },
        ];
    }
}
