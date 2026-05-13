<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\ClubUserFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Override;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property-read int $id
 * @property-read string $email
 * @property-read CarbonInterface|null $email_verified_at
 * @property-read string $password
 * @property-read string|null $remember_token
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Collection<int, Club> $clubs
 */
final class ClubUser extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;

    /** @use HasFactory<ClubUserFactory> */
    use HasFactory;

    use MustVerifyEmailTrait;
    use Notifiable;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    #[Override]
    protected $fillable = [
        'email',
        'password',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'int',
            'email' => 'string',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Determine if the user owns the given model.
     */
    public function owns(Model $model, string $relation = 'clubUser'): bool
    {
        return $model->{$relation}()->is($this);
    }

    /**
     * Get the clubs for the club user.
     *
     * @return HasMany<Club,$this>
     */
    public function clubs(): HasMany
    {
        return $this->hasMany(Club::class);
    }
}
