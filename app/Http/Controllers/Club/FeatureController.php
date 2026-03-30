<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Resources\Admin\FeatureResource;
use App\Models\Feature;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class FeatureController
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
