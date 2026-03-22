<?php

declare(strict_types=1);

namespace App\Enums\Traits;

trait EnumHasLabels
{
    /** @return array<string, string> */
    public static function labels(): array
    {
        return collect(static::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->all();
    }

    /** @return array<int, string> */
    public static function values(): array
    {
        return collect(static::cases())
            ->map(fn ($case) => $case->value)
            ->all();
    }

    public function label(): string
    {
        return __($this->value);
    }

    /** @return array<string, string> */
    public function mapKeyLabel(): array
    {
        return [$this->value => $this->label()];
    }
}
