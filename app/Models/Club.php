<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\ClubFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

/**
 * @property-read int $id
 * @property-read int $club_user_id
 * @property-read string $address_city
 * @property-read string $address_country
 * @property-read string $address_postal_code
 * @property-read string $address_state
 * @property-read string $address_street
 * @property-read string $description
 * @property-read string|null $facebook_url
 * @property-read string|null $instagram_url
 * @property-read string|null $latitude
 * @property-read string|null $longitude
 * @property-read string|null $operating_hours_additional_info
 * @property-read string $organization_name
 * @property-read string|null $phone_number
 * @property-read string|null $reservation_policies_and_payment_terms
 * @property-read string|null $twitter_url
 * @property-read string|null $whatsapp_number
 * @property-read bool $is_active
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read CarbonInterface $deleted_at
 * @property-read Collection<int, ClubWorkingDay> $workingDays
 * @property-read Collection<int, ClubService> $services
 * @property-read Collection<int, Court> $courts
 */
final class Club extends Model
{
    /** @use HasFactory<ClubFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @var list<string>
     */
    #[Override]
    protected $fillable = [
        'club_user_id',
        'address_city',
        'address_country',
        'address_postal_code',
        'address_state',
        'address_street',
        'description',
        'facebook_url',
        'instagram_url',
        'latitude',
        'longitude',
        'operating_hours_additional_info',
        'organization_name',
        'phone_number',
        'reservation_policies_and_payment_terms',
        'twitter_url',
        'whatsapp_number',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'int',
            'club_user_id' => 'int',
            'address_city' => 'string',
            'address_country' => 'string',
            'address_postal_code' => 'string',
            'address_state' => 'string',
            'address_street' => 'string',
            'description' => 'string',
            'facebook_url' => 'string',
            'instagram_url' => 'string',
            'latitude' => 'string',
            'longitude' => 'string',
            'operating_hours_additional_info' => 'string',
            'organization_name' => 'string',
            'phone_number' => 'string',
            'reservation_policies_and_payment_terms' => 'string',
            'twitter_url' => 'string',
            'whatsapp_number' => 'string',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the club user that owns this club.
     *
     * @return BelongsTo<ClubUser, $this>
     */
    public function clubUser(): BelongsTo
    {
        return $this->belongsTo(ClubUser::class);
    }

    /**
     * Get the working days for this club.
     *
     * @return HasMany<ClubWorkingDay, $this>
     */
    public function workingDays(): HasMany
    {
        return $this->hasMany(ClubWorkingDay::class);
    }

    /**
     * Get the services for this club.
     *
     * @return HasMany<ClubService, $this>
     */
    public function services(): HasMany
    {
        return $this->hasMany(ClubService::class);
    }

    /**
     * Get the courts for this club.
     *
     * @return HasMany<Court, $this>
     */
    public function courts(): HasMany
    {
        return $this->hasMany(Court::class);
    }
}
