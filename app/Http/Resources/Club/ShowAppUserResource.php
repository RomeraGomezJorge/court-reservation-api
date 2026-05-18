<?php

declare(strict_types=1);

namespace App\Http\Resources\Club;

use App\Models\AppUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AppUser
 */
final class ShowAppUserResource extends JsonResource
{
    public function __construct(AppUser $resource)
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
            'last_name' => $this->last_name,
            'phone_number' => $this->phone_number,
            'birthday' => $this->birthday->format('Y-m-d'),
            'gender' => $this->gender->value,
            'email' => $this->email,
            'club_ids' => $this->clubs()->pluck('club_id'),
        ];
    }
}
