<?php

declare(strict_types=1);

namespace App\Http\Requests\Club;

use App\Models\ClubUser;
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

                $clubUser = ClubUser::query()->where('email', $this->email)->first();

                if ($clubUser === null || ! Hash::check($this->password, $clubUser->password)) {
                    $validator->errors()->add(
                        'password',
                        __('auth.failed')
                    );

                    return;
                }

                if (! $clubUser->hasVerifiedEmail()) {
                    $validator->errors()->add(
                        'email',
                        __('auth.email_not_verified')
                    );
                }
            },
        ];
    }
}
