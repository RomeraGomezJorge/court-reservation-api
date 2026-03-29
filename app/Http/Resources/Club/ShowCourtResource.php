<?php

declare(strict_types=1);

namespace App\Http\Resources\Club;

use App\Models\Court;
use App\Models\Feature;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Court
 */
final class ShowCourtResource extends JsonResource
{
    public function __construct(Court $resource)
    {
        parent::__construct($resource);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'club_id' => $this->club_id,
            'sport_type_id' => $this->sport_type_id,
            'name' => $this->name,
            'description' => $this->description,
            'is_available' => $this->is_available,
            'sport_type' => [
                'id' => $this->sportType->id,
                'name' => $this->sportType->name,
            ],
            'features' => $this->features->map(fn (Feature $feature): array => [
                'id' => $feature->id,
                'name' => $feature->name,
            ])->values(),
        ];
    }
}
