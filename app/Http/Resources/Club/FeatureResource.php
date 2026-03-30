<?php

declare(strict_types=1);

namespace App\Http\Resources\Club;

use App\Models\Feature;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Feature
 */
final class FeatureResource extends JsonResource
{
    public function __construct(Feature $resource)
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
        ];
    }
}
