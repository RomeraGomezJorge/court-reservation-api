<?php

declare(strict_types=1);

namespace App\Http\Resources\Club;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ShowCourtPriceRuleResource extends JsonResource
{
    /** @param  array<string, mixed>  $resource */
    public function __construct(array $resource)
    {
        parent::__construct($resource);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->resource;

        return [
            'court_id' => $data['court_id'],
            'play_time_minutes' => $data['play_time_minutes'],
            'price_starts_at' => $data['price_starts_at'],
            'days' => $data['days'],
        ];
    }
}
