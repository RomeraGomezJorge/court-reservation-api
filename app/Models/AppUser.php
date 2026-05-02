<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Gender;
use Carbon\CarbonInterface;
use Database\Factories\AppUserFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Override;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $last_name
 * @property-read string $phone_number
 * @property-read CarbonInterface $birthday
 * @property-read Gender $gender
 * @property-read string $email
 * @property-read CarbonInterface|null $email_verified_at
 * @property-read string $password
 * @property-read string|null $remember_token
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read CarbonInterface|null $deleted_at
 * @property-read Collection<int, Club> $clubs
 */
final class AppUser extends Authenticatable implements CanResetPassword, MustVerifyEmail
{
    use CanResetPasswordTrait;
    use HasApiTokens;

    /** @use HasFactory<AppUserFactory> */
    use HasFactory;

    use MustVerifyEmailTrait;
    use Notifiable;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    #[Override]
    protected $fillable = [
        'name',
        'last_name',
        'phone_number',
        'birthday',
        'gender',
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
            'name' => 'string',
            'last_name' => 'string',
            'phone_number' => 'string',
            'birthday' => 'date',
            'gender' => Gender::class,
            'email' => 'string',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the clubs that this app user belongs to.
     *
     * @return BelongsToMany<Club, $this>
     */
    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'app_user_club');
    }

    /**
     * Determine if the user owns the given model.
     */
    public function owns(Model $model, string $relation = 'appUser'): bool
    {
        return $model->{$relation}()->is($this);
    }
}
