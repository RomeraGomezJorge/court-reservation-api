<?php

declare(strict_types=1);

namespace App\Http\Requests\Club;

use App\Enums\Gender;
use App\Models\AppUser;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Unique;

final class UpdateAppUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, ValidationRule|string|Enum|Unique>> */
    public function rules(): array
    {

        /** @var AppUser $appUser */
        $appUser = $this->route('app_user');

        return [
            'name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone_number' => [
                'required',
                'string',
                'max:100',
                'unique:app_users,phone_number,'.$appUser->id,
            ],
            'email' => [
                'nullable',
                'email',
                'max:100',
                'unique:app_users,email,'.$appUser->id,
            ],
            'birthday' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', Rule::enum(Gender::class)],
        ];
    }
}
