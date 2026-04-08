<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ClubServicesType;
use Carbon\CarbonInterface;
use Database\Factories\ClubServiceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property-read int $id
 * @property-read int $club_id
 * @property-read ClubServicesType $type
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class ClubService extends Model
{
    /** @use HasFactory<ClubServiceFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    #[Override]
    protected $fillable = [
        'club_id',
        'type',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'int',
            'club_id' => 'int',
            'type' => ClubServicesType::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the club that owns this club service.
     *
     * @return BelongsTo<Club, $this>
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }
}
