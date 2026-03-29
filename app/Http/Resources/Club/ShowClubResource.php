<?php

declare(strict_types=1);

namespace App\Http\Resources\Club;

use Illuminate\Support\Facades\Date;
use App\Models\Club;
use App\Models\ClubService;
use App\Models\ClubWorkingDay;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Club
 */
final class ShowClubResource extends JsonResource
{
    public function __construct(Club $resource)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'club_user_id' => $this->club_user_id,
            'address_city' => $this->address_city,
            'address_country' => $this->address_country,
            'address_postal_code' => $this->address_postal_code,
            'address_state' => $this->address_state,
            'address_street' => $this->address_street,
            'description' => $this->description,
            'facebook_url' => $this->facebook_url,
            'instagram_url' => $this->instagram_url,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'operating_hours_additional_info' => $this->operating_hours_additional_info,
            'organization_name' => $this->organization_name,
            'phone_number' => $this->phone_number,
            'reservation_policies_and_payment_terms' => $this->reservation_policies_and_payment_terms,
            'twitter_url' => $this->twitter_url,
            'whatsapp_number' => $this->whatsapp_number,
            'is_active' => $this->is_active,
            'working_days' => $this->workingDays->map(fn(ClubWorkingDay $day): array => [
                'day' => $day->day->value,
                'opening_hour' => $this->formatHour($day->opening_hour),
                'closing_hour' => $this->formatHour($day->closing_hour),
            ])->values(),
            'services' => $this->services->map(fn(ClubService $service): array => [
                'id' => $service->id,
                'type' => $service->type->value,
                'icon' => $service->type->getIcon(),
            ])->values(),
        ];
    }

    private function formatHour(string $hour): string
    {
        $parsedHour = Date::createFromFormat('H:i:s', $hour);

        return $parsedHour instanceof Date ? $parsedHour->format('H:i') : $hour;
    }
}
