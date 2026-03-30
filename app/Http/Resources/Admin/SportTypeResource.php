<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\SportType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SportType
 */
final class SportTypeResource extends JsonResource
{
    public function __construct(SportType $resource)
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
            'name' => $this->name,
            'is_active' => $this->is_active,
        ];
    }
}
