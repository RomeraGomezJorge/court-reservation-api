<?php

declare(strict_types=1);

namespace App\Http\Requests\Club;

use App\Enums\ClubWorkingDays;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class StoreClubRequest extends FormRequest
{
    private const MAX_WORKING_HOURS_IN_SECONDS = 57600;

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
            'working_days' => ['required', 'array', 'min:1'],
            'working_days.*.day' => ['required', 'distinct', Rule::enum(ClubWorkingDays::class)],
            'working_days.*.opening_hour' => ['required', 'date_format:H:i'],
            'working_days.*.closing_hour' => ['required', 'date_format:H:i'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $this->validateWorkingDays($validator);
            },
        ];
    }

    public function clubData(): array
    {
        return $this->safe()->except([
            'working_days',
        ]);
    }

    public function workingDays(): array
    {
        return $this->input('working_days', []);
    }

    private function validateWorkingDays(Validator $validator): void
    {
        $workingDays = $this->workingDays();

        foreach ($workingDays as $index => $workingDay) {
            $this->validateWorkingHours($validator, $workingDay, $index);
        }
    }

    private function validateWorkingHours(Validator $validator, array $workingDay, int $index): void
    {
        if (! isset($workingDay['opening_hour'], $workingDay['closing_hour'])) {
            return;
        }

        $day = $workingDay['day'] ?? 'desconocido';

        try {
            $opening = Carbon::createFromFormat('H:i', $workingDay['opening_hour']);
            $closing = Carbon::createFromFormat('H:i', $workingDay['closing_hour']);
        } catch (Exception) {
            $day = $workingDay['day'] ?? 'desconocido';
            $validator->errors()->add(
                "working_days.$index.opening_hour",
                "El formato de hora de {$day} es inválido."
            );

            return;
        }

        if ($this->isInvalidRange($opening, $closing)) {

            $validator->errors()->add(
                "working_days.$index.closing_hour",
                "El horario para {$day} es demasiado amplio."
            );
        }
    }

    private function isInvalidRange(Carbon $opening, Carbon $closing): bool
    {
        if ($closing->lessThanOrEqualTo($opening)) {
            $closing = $closing->copy()->addDay();
        }

        $diffInSeconds = $opening->diffInSeconds($closing);

        return $diffInSeconds <= 0 || $diffInSeconds > self::MAX_WORKING_HOURS_IN_SECONDS;
    }
}
