<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\CourtFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

/**
 * @property-read int $id
 * @property-read int $club_id
 * @property-read int $sport_type_id
 * @property-read string $name
 * @property-read string|null $description
 * @property-read bool $is_available
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read CarbonInterface|null $deleted_at
 * @property-read Club $club
 * @property-read SportType $sportType
 * @property-read Collection<int, Feature> $features
 * @property-read Collection<int, CourtPriceRule> $priceRules
 */
final class Court extends Model
{
    /** @use HasFactory<CourtFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @var list<string>
     */
    #[Override]
    protected $fillable = [
        'club_id',
        'sport_type_id',
        'name',
        'description',
        'is_available',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'club_id' => 'string',
            'sport_type_id' => 'integer',
            'name' => 'string',
            'description' => 'string',
            'is_available' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Club, $this>
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * @return BelongsTo<SportType, $this>
     */
    public function sportType(): BelongsTo
    {
        return $this->belongsTo(SportType::class);
    }

    /**
     * @return BelongsToMany<Feature, $this>
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class);
    }

    /**
     * Get the price rules for this court.
     *
     * @return HasMany<CourtPriceRule, $this>
     */
    public function priceRules(): HasMany
    {
        return $this->hasMany(CourtPriceRule::class);
    }
}
