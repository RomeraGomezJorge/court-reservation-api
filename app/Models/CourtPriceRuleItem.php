<?php

declare(strict_types=1);

namespace App\Models;

use App\Builders\CourtPriceRuleItemBuilder;
use App\Enums\PlayTime;
use Carbon\CarbonInterface;
use Database\Factories\CourtPriceRuleItemFactory;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property-read int $id
 * @property-read int $court_price_rule_id
 * @property-read PlayTime $play_time_minutes
 * @property-read string $price
 * @property-read CarbonInterface $price_starts_at
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read CourtPriceRule $priceRule
 */
#[UseEloquentBuilder(CourtPriceRuleItemBuilder::class)]
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
            'id' => 'int',
            'court_price_rule_id' => 'int',
            'play_time_minutes' => PlayTime::class,
            'price' => 'decimal:2',
            'price_starts_at' => 'datetime:H:i:s',
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
