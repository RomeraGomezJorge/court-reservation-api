<?php

declare(strict_types=1);

namespace App\Http\Requests\Club;

use App\Enums\Gender;
use App\Models\AppUser;
use App\Models\Club;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\Validator;

/**
 * @property string $name
 * @property string $last_name
 * @property string $phone_number
 * @property string $email
 * @property string $birthday
 * @property string $gender
 * @property array<int,int> $club_ids
 */
final class StoreAppUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, ValidationRule|string|Enum|Unique>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone_number' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:100'],
            'birthday' => ['required', 'date', 'before_or_equal:today'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'club_ids' => ['required', 'distinct', 'array', 'exists:clubs,id'],
        ];
    }

    /**
     * @return array{
     *     name: string,
     *     last_name: string,
     *     phone_number: string,
     *     birthday: string,
     *     gender: string,
     *     email: string,
     *     club_ids: array<int,int>
     * }
     */
    public function validatedAttributes(): array
    {
        return [
            'name' => (string) $this->validated('name'),
            'last_name' => (string) $this->validated('last_name'),
            'phone_number' => (string) $this->validated('phone_number'),
            'birthday' => (string) $this->validated('birthday'),
            'gender' => (string) $this->validated('gender'),
            'email' => (string) $this->validated('email'),
            'club_ids' => (array) $this->validated('club_ids'),
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->validateAppUserIsNotAlreadyAttachedToClub($validator);
                $this->validateClubIdsBelongToAuthenticatedClubUser($validator);
            },
        ];
    }

    private function validateAppUserIsNotAlreadyAttachedToClub(Validator $validator): void
    {
        $appUserId = AppUser::query()
            ->where('email', $this->email)
            ->value('id');

        if (! $appUserId) {
            return;
        }

        $clubIds = Club::query()
            ->where('club_user_id', Auth::id())
            ->pluck('id');

        if ($clubIds->isEmpty()) {
            return;
        }

        $appUserBelongsToClub = DB::table('app_user_club')
            ->whereIn('club_id', $clubIds)
            ->where('app_user_id', $appUserId)
            ->exists();

        if ($appUserBelongsToClub) {
            $validator->errors()->add(
                'email',
                __('validation.app_user_already_belongs_to_club'),
            );
        }
    }

    private function validateClubIdsBelongToAuthenticatedClubUser(
        Validator $validator,
    ): void {
        $allowedClubIds = Club::query()
            ->where('club_user_id', Auth::id())
            ->whereIn('id', $this->club_ids)
            ->pluck('id');

        $invalidClubIds = collect($this->club_ids)
            ->diff($allowedClubIds)
            ->values();

        if ($invalidClubIds->isEmpty()) {
            return;
        }

        $validator->errors()->add(
            'club_ids',
            __(
                'validation.club_ids_not_owned_by_user',
                [
                    'club_ids' => $invalidClubIds->join(', '),
                ],
            ),
        );
    }
}
