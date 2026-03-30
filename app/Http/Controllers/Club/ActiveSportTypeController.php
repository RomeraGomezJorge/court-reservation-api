<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Resources\Club\SportTypeResource;
use App\Models\SportType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ActiveSportTypeController
{
    public function index(): AnonymousResourceCollection
    {
        return SportTypeResource::collection(
            SportType::query()
                ->where('is_active', true)
                ->get(),
        );
    }
}
