<?php

declare(strict_types=1);

namespace Database\Helpers;

use Illuminate\Support\Facades\App;

final class MigrationHelper
{
    public static function shouldRunInTesting(): bool
    {
        return !App::environment('testing');
    }
}
