<?php

declare(strict_types=1);

namespace App\Enums;

enum PlayTime: int
{
    case TenMinutes = 10;
    case ThirtyMinutes = 30;
    case FortyFiveMinutes = 45;
    case SixtyMinutes = 60;
    case NinetyMinutes = 90;
    case OneHundredTwentyMinutes = 120;

    public function label(): string
    {
        return "{$this->value} min";
    }
}
