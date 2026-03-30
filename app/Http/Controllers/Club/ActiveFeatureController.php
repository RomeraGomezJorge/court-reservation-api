<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Resources\Club\FeatureResource;
use App\Models\Feature;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ActiveFeatureController
{
    public function index(): AnonymousResourceCollection
    {
        return FeatureResource::collection(
            Feature::query()
                ->where('is_active', true)
                ->get(),
        );
    }
}
