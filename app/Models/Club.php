<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\ClubFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Override;

/**
 * @property-read string $id
 * @property-read string $email
 * @property-read string $password
 * @property-read string $address_city
 * @property-read string $address_country
 * @property-read string $address_postal_code
 * @property-read string $address_state
 * @property-read string $address_street
 * @property-read string $description
 * @property-read string|null $facebook_url
 * @property-read string|null $instagram_url
 * @property-read string $latitude
 * @property-read string $longitude
 * @property-read string|null $operating_hours_additional_info
 * @property-read string $organization_name
 * @property-read string|null $phone_number
 * @property-read string $reservation_policies_and_payment_terms
 * @property-read string|null $twitter_url
 * @property-read string|null $whatsapp_number
 * @property-read bool $is_active
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Club extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<ClubFactory> */
    use HasFactory;

    use Notifiable;

    /**
     * @var list<string>
     */
    #[Override]
    protected $fillable = [
        'email',
        'password',
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
            'id' => 'string',
            'email' => 'string',
            'password' => 'hashed',
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
        ];
    }
}
