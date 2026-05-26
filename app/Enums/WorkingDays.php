<?php

declare(strict_types=1);

namespace App\Enums;

enum WorkingDays: string
{
    case Monday = 'monday';
    case Tuesday = 'tuesday';
    case Wednesday = 'wednesday';
    case Thursday = 'thursday';
    case Friday = 'friday';
    case Saturday = 'saturday';
    case Sunday = 'sunday';
    case Holiday = 'holiday';

    /**
     * @return array<int, string>
     */

    public static function values(): array
    {
        return collect(self::cases())->map(fn ($value) => $value->value)->all();
    }

    public function label(): string
    {
        return __('club-working-days.'.$this->value);
    }
}
