<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Feature;
use Symfony\Component\HttpFoundation\Response;

final class FeatureStatusToggleController
{
    public function __invoke(Feature $feature): Response
    {
        $feature->update([
            'is_active' => ! $feature->is_active,
        ]);

        return new Response(status: 204);
    }
}
