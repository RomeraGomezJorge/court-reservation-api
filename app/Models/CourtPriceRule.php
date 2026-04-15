<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CourtPriceRuleDay;
use Carbon\CarbonInterface;
use Database\Factories\CourtPriceRuleFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property-read int $id
 * @property-read int $court_id
 * @property-read CourtPriceRuleDay $day
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Court $court
 * @property-read Collection<int, CourtPriceRuleItem> $items
 */
final class CourtPriceRule extends Model
{
    /** @use HasFactory<CourtPriceRuleFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    #[Override]
    protected $fillable = [
        'court_id',
        'day',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'int',
            'court_id' => 'int',
            'day' => CourtPriceRuleDay::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the court that owns this price rule.
     *
     * @return BelongsTo<Court, $this>
     */
    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    /**
     * Get the price rule items for this rule.
     *
     * @return HasMany<CourtPriceRuleItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CourtPriceRuleItem::class);
    }
}
