<?php

declare(strict_types=1);

namespace App\Http\Resources\Club;

use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Court
 */
final class CourtResource extends JsonResource
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
            'name' => $this->name,
            'is_available' => $this->is_available,
        ];
    }
}
