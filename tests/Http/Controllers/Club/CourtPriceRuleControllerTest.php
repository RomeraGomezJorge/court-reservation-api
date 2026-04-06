<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Club;

use App\Http\Controllers\Club\CourtPriceRuleController;
use App\Models\Club;
use App\Models\Court;
use App\Models\CourtPriceRule;
use App\Models\CourtPriceRuleItem;
use App\Models\SportType;

use function Pest\Laravel\post;

function validPriceRulesPayload(Court $court): array
{
    return [
        'court_id' => $court->id,
        'rules' => [
            [
                'day' => null,
                'items' => [
                    [
                        'play_time_minutes' => 60,
                        'prices' => [
                            ['starts_at' => '00:00', 'price' => 1000],
                            ['starts_at' => '12:00', 'price' => 1200],
                        ],
                    ],
                    [
                        'play_time_minutes' => 90,
                        'prices' => [
                            ['starts_at' => '00:00', 'price' => 1400],
                        ],
                    ],
                ],
            ],
            [
                'day' => 'monday',
                'items' => [
                    [
                        'play_time_minutes' => 60,
                        'prices' => [
                            ['starts_at' => '00:00', 'price' => 900],
                        ],
                    ],
                ],
            ],
        ],
    ];
}

beforeEach(function (): void {
    $this->clubUser = actingAsClubUser();
});

it('stores price rules for a court', function (): void {
    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->create();

    $court = Court::factory()->create([
        'club_id' => $club->id,
        'sport_type_id' => $sportType->id,
    ]);

    post(action([CourtPriceRuleController::class, 'store'], ['club' => $club, 'court' => $court]), validPriceRulesPayload($court))
        ->assertStatus(201);

    $this->assertDatabaseHas('court_price_rules', [
        'court_id' => $court->id,
        'day' => null,
    ]);

    $this->assertDatabaseHas('court_price_rules', [
        'court_id' => $court->id,
        'day' => 'monday',
    ]);

    $genericRule = CourtPriceRule::query()
        ->where('court_id', $court->id)
        ->whereNull('day')
        ->firstOrFail();

    $this->assertDatabaseHas('court_price_rule_items', [
        'court_price_rule_id' => $genericRule->id,
        'play_time_minutes' => 60,
        'price_starts_at' => '12:00:00',
        'price' => 1200,
    ]);
});

it('replaces old data on each store call', function (): void {
    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->create();

    $court = Court::factory()->create([
        'club_id' => $club->id,
        'sport_type_id' => $sportType->id,
    ]);

    $oldRule = CourtPriceRule::factory()->forCourt($court)->generic()->create();
    CourtPriceRuleItem::factory()->forRule($oldRule)->forPlayTimeMinutes(60)->startingAt('08:00:00')->create([
        'price' => 500,
    ]);

    post(action([CourtPriceRuleController::class, 'store'], ['club' => $club, 'court' => $court]), validPriceRulesPayload($court))
        ->assertStatus(201);

    $this->assertDatabaseMissing('court_price_rules', [
        'id' => $oldRule->id,
    ]);

    $this->assertDatabaseMissing('court_price_rule_items', [
        'court_price_rule_id' => $oldRule->id,
    ]);
});

it('fails when court_id does not match route court', function (): void {
    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->create();

    $court = Court::factory()->create([
        'club_id' => $club->id,
        'sport_type_id' => $sportType->id,
    ]);

    $otherCourt = Court::factory()->create([
        'club_id' => $club->id,
        'sport_type_id' => $sportType->id,
    ]);

    $payload = validPriceRulesPayload($court);
    $payload['court_id'] = $otherCourt->id;

    post(action([CourtPriceRuleController::class, 'store'], ['club' => $club, 'court' => $court]), $payload)
        ->assertExactJson([
            'code' => 422,
            'messages' => ['El campo cancha debe coincidir con la cancha de la ruta.'],
        ]);
});

it('fails to store price rules when the court does not belong to the club in route', function (): void {
    $ownedClub = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
    ]);

    $otherOwnedClub = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->create();

    $court = Court::factory()->create([
        'club_id' => $otherOwnedClub->id,
        'sport_type_id' => $sportType->id,
    ]);

    post(action([CourtPriceRuleController::class, 'store'], ['club' => $ownedClub, 'court' => $court]), validPriceRulesPayload($court))
        ->assertNotFound()
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso Court  no se ha encontrado.'],
        ]);
});
