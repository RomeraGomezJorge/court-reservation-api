<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\SportType;
use Symfony\Component\HttpFoundation\Response;

final class SportTypeStatusToggleController
{
    public function __invoke(SportType $sportType): Response
    {
        $sportType->update([
            'is_active' => ! $sportType->is_active,
        ]);

        return new Response(status: 204);
    }
}
