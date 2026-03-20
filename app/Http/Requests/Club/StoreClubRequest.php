<?php

declare(strict_types=1);

namespace App\Http\Requests\Club;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreClubRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, ValidationRule|string>> */
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
        ];
    }
}
