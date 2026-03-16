<?php

declare(strict_types=1);

namespace App\Http\Resources\Club;

use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Club
 */
final class ClubResource extends JsonResource
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
            'organization_name' => $this->organization_name,
            'is_active' => $this->is_active,
        ];
    }
}
