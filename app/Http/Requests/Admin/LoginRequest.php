<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
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

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {

                if ($validator->errors()->count() > 0) {
                    return;
                }

                $user = User::query()->where('email', $this->email)->first();

                if ($user === null || ! Hash::check($this->password, $user->password)) {
                    $validator->errors()->add(
                        'password',
                        __('auth.failed')
                    );

                    return;
                }

                if (! $user->hasVerifiedEmail()) {
                    $validator->errors()->add(
                        'email',
                        __('auth.email_not_verified')
                    );
                }
            },
        ];
    }
}
