<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\ServicerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property-read string $id
 * @property-read non-empty-string $name
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Service extends Model
{
    /** @use HasFactory<ServicerFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    #[Override]
    protected $fillable = [
        'name',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'name' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
