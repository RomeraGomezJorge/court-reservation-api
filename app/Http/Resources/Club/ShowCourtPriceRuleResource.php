<?php

declare(strict_types=1);

namespace App\Http\Resources\Club;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin array{
 *     court_id: int|string,
 *     play_time: array<int, int>,
 *     price_starts_at: array<int, string>,
 *     days: array<int, array{
 *         day: string|null,
 *         label: string,
 *         time_slots: array<int, array{
 *             label: string,
 *             starts_at: string,
 *             prices: array<string, int|float|null>
 *         }>
 *     }>
 * }
 */
final class ShowCourtPriceRuleResource extends JsonResource
{
    /** @param array<string, mixed> $resource */
    public function __construct(array $resource)
    {
        parent::__construct($resource);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $data */
        $data = $this->resource;

        return $data;
    }
}

