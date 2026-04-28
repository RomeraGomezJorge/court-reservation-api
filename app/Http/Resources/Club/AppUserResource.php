<?php

declare(strict_types=1);

namespace App\Http\Resources\Club;

use App\Models\AppUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;

/**
 * @mixin AppUser
 */
final class AppUserResource extends JsonResource
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
            'birthday' => Date::parse($this->birthday)->toDateString(),
            'gender' => $this->gender->value,
            'email' => $this->email,
        ];
    }
}
