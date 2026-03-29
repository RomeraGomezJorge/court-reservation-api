<?php

declare(strict_types=1);

namespace App\Http\Requests\Club;

use App\Enums\WorkingDays;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\Validator;

abstract class BaseClubRequest extends FormRequest
{
    protected const MAX_WORKING_HOURS_IN_SECONDS = 57600;

    /** @return array<string, mixed> */
    final public function clubData(): array
    {
        return $this->safe()->except([
            'working_days',
            'services',
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    final public function workingDays(): array
    {
        $workingDays = $this->input('working_days', []);

        return is_array($workingDays) ? $workingDays : [];
    }

    /** @return array<int, string> */
    final public function services(): array
    {
        $services = $this->input('services', []);

        return is_array($services) ? $services : [];
    }

    protected function validateWorkingDays(Validator $validator): void
    {
        $workingDays = $this->workingDays();

        foreach ($workingDays as $index => $workingDay) {
            $this->validateWorkingHours($validator, $workingDay, $index);
        }
    }

    /** @param array<string, mixed> $workingDay */
    protected function validateWorkingHours(Validator $validator, array $workingDay, int $index): void
    {
        if (! isset($workingDay['opening_hour'], $workingDay['closing_hour'])) {
            return;
        }

        $day = is_string($workingDay['day'] ?? null) ? $workingDay['day'] : 'unknown';
        $dayLabel = WorkingDays::tryFrom($day)?->label() ?? $day;

        try {
            $opening = Date::createFromFormat('H:i', $workingDay['opening_hour']);
            $closing = Date::createFromFormat('H:i', $workingDay['closing_hour']);

        } catch (Exception) {
            $validator->errors()->add(
                "working_days.{$index}.opening_hour",
                __('club_working_day.invalid_time_format', ['day' => $dayLabel])
            );

            return;
        }

        if (! $opening instanceof CarbonImmutable || ! $closing instanceof CarbonImmutable) {
            $validator->errors()->add(
                "working_days.{$index}.opening_hour",
                __('club_working_day.invalid_time_format', ['day' => $dayLabel])
            );

            return;
        }

        if ($this->isInvalidRange($opening, $closing)) {
            $validator->errors()->add(
                "working_days.{$index}.closing_hour",
                __('club_working_day.range_too_wide', ['day' => $dayLabel])
            );
        }
    }

    protected function isInvalidRange(CarbonImmutable $opening, CarbonImmutable $closing): bool
    {
        if ($closing->lessThanOrEqualTo($opening)) {
            $closing = $closing->copy()->addDay();
        }

        $diffInSeconds = $opening->diffInSeconds($closing);

        return $diffInSeconds <= 0 || $diffInSeconds > self::MAX_WORKING_HOURS_IN_SECONDS;
    }
}
