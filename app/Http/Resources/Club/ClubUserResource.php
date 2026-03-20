<?php

declare(strict_types=1);

namespace App\Http\Resources\Club;

use App\Models\ClubUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ClubUser
 */
final class ClubUserResource extends JsonResource
{
    public function __construct(ClubUser $resource)
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
            'email' => $this->email,
            'roles' => ['club'],
        ];
    }
}
