<?php

declare(strict_types=1);

namespace App\Http\Requests\Club;

use App\Enums\ClubServicesType;
use App\Enums\WorkingDays;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;
use Illuminate\Validation\Validator;

final class StoreClubRequest extends BaseClubRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, ValidationRule|string|In>> */
    public function rules(): array
    {
        return [
            'address_city' => ['required', 'string', 'max:255'],
            'address_country' => ['required', 'string', 'max:255'],
            'address_postal_code' => ['required', 'string', 'max:255'],
            'address_state' => ['required', 'string', 'max:255'],
            'address_street' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'operating_hours_additional_info' => ['nullable', 'string', 'max:255'],
            'organization_name' => ['required', 'string', 'max:255', 'unique:clubs,organization_name'],
            'phone_number' => ['nullable', 'string', 'max:255'],
            'reservation_policies_and_payment_terms' => ['nullable', 'string', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'whatsapp_number' => ['nullable', 'string', 'max:255'],
            'working_days' => ['required', 'array', 'min:1'],
            'working_days.*.day' => ['required', 'distinct', Rule::in(WorkingDays::values())],
            'working_days.*.opening_hour' => ['required', 'date_format:H:i'],
            'working_days.*.closing_hour' => ['required', 'date_format:H:i'],
            'services' => ['nullable', 'array'],
            'services.*' => ['nullable', 'distinct', Rule::in(ClubServicesType::values())],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->validateWorkingDays($validator);
            },
        ];
    }
}
