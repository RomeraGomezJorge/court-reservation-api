<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FeatureFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Override;

final class Feature extends Model
{
    /** @use HasFactory<FeatureFactory> */
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
