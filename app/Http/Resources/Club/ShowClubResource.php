<?php

declare(strict_types=1);

namespace App\Http\Resources\Club;

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
            'working_days' => $this->workingDays->map(function (ClubWorkingDay $day) {
                return [
                    'day' => $day->day->value,
                    'opening_hour' => Carbon::createFromFormat('H:i:s', $day->opening_hour)->format('H:i'),
                    'closing_hour' => Carbon::createFromFormat('H:i:s', $day->closing_hour)->format('H:i'),
                ];
            })->values(),
            'services' => $this->services->map(function (ClubService $service) {
                return [
                    'id' => $service->id,
                    'type' => $service->type->value,
                    'icon' => $service->type->getIcon(),
                ];
            })->values(),
        ];
    }
}
