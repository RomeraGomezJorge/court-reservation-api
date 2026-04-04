<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\CourtPriceRuleItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property-read string $id
 * @property-read string $court_price_rule_id
 * @property-read int $play_time_minutes
 * @property-read string $price
 * @property-read string $price_starts_at
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read CourtPriceRule $priceRule
 */
final class CourtPriceRuleItem extends Model
{
    /** @use HasFactory<CourtPriceRuleItemFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    #[Override]
    protected $fillable = [
        'court_price_rule_id',
        'play_time_minutes',
        'price',
        'price_starts_at',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'court_price_rule_id' => 'string',
            'play_time_minutes' => 'integer',
            'price' => 'decimal:2',
            'price_starts_at' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the price rule that owns this item.
     *
     * @return BelongsTo<CourtPriceRule, $this>
     */
    public function priceRule(): BelongsTo
    {
        return $this->belongsTo(CourtPriceRule::class, 'court_price_rule_id');
    }
}
