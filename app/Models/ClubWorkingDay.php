<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WorkingDays;
use Carbon\CarbonInterface;
use Database\Factories\ClubWorkingDayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property-read int $id
 * @property-read int $club_id
 * @property-read WorkingDays $day
 * @property-read string $opening_hour
 * @property-read string $closing_hour
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class ClubWorkingDay extends Model
{
    /** @use HasFactory<ClubWorkingDayFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    #[Override]
    protected $fillable = [
        'club_id',
        'day',
        'opening_hour',
        'closing_hour',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'club_id' => 'string',
            'day' => WorkingDays::class,
            'opening_hour' => 'string',
            'closing_hour' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the club that owns this working day.
     *
     * @return BelongsTo<Club, $this>
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
