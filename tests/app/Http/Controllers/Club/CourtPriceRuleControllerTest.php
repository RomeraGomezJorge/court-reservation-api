<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Club;

use App\Http\Controllers\Club\CourtPriceRuleController;
use App\Http\Requests\Club\StoreCourtPriceRuleRequest;
use App\Http\Resources\Club\ShowCourtPriceRuleResource;
use App\Models\Club;
use App\Models\Court;
use App\Models\CourtPriceRule;
use App\Models\CourtPriceRuleItem;
use App\Models\SportType;
use App\Services\CourtPriceRulesShowBuilderService;
use App\Services\OwnershipVerifierService;
use Closure;
use Illuminate\Database\Eloquent\Factories\Sequence;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

mutates(CourtPriceRuleController::class);
mutates(StoreCourtPriceRuleRequest::class);
mutates(OwnershipVerifierService::class);
mutates(CourtPriceRulesShowBuilderService::class);
mutates(ShowCourtPriceRuleResource::class);



function validPriceRulesPayload(Court $court): array
{
    return [
        'court_id' => $court->id,
        'rules' => [
            [
                'day' => 'base',
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

/** @return array{Club, Court} */
function createClubAndCourtForPriceRuleTests(int $clubUserId): array
{
    $club = Club::factory()->create([
        'club_user_id' => $clubUserId,
    ]);

    $sportType = SportType::factory()->create();

    $court = Court::factory()->create([
        'club_id' => $club->id,
        'sport_type_id' => $sportType->id,
    ]);

    return [$club, $court];
}

beforeEach(function (): void {
    $this->clubUser = actingAsClubUser();
});

it('validates store payload structure and scalar constraints', function (Closure $mutatePayload, string $expectedMessage): void {
    [$club, $court] = createClubAndCourtForPriceRuleTests($this->clubUser->id);

    $payload = validPriceRulesPayload($court);
    $payload = $mutatePayload($payload, $court);

    $response = post(action([CourtPriceRuleController::class, 'store'], ['club' => $club, 'court' => $court]), $payload);

    $response->assertStatus(422);
    expect($response->json('code'))->toBe(422);
    expect($response->json('messages'))->toContain($expectedMessage);
})->with([
    'missing court_id' => [
        fn (array $payload): array => tap($payload, fn (&$p) => unset($p['court_id'])),
        'El campo cancha es obligatorio.',
    ],
    'court_id must be integer' => [
        fn (array $payload): array => tap($payload, fn (&$p) => $p['court_id'] = 'abc'),
        'El campo cancha debe ser un número entero.',
    ],
    'court_id must exist' => [
        fn (array $payload): array => tap($payload, fn (&$p) => $p['court_id'] = 99999999),
        'El campo cancha no existe.',
    ],
    'missing rules' => [
        fn (array $payload): array => tap($payload, fn (&$p) => unset($p['rules'])),
        'El campo reglas de precios es obligatorio.',
    ],
    'rules must be array' => [
        fn (array $payload): array => tap($payload, fn (&$p) => $p['rules'] = 'invalid'),
        'El campo reglas de precios debe ser un conjunto.',
    ],
    'rules must have at least one item' => [
        fn (array $payload): array => tap($payload, fn (&$p) => $p['rules'] = []),
        'El campo reglas de precios debe tener al menos 1 elementos.',
    ],
    'missing day' => [
        fn (array $payload): array => tap($payload, fn (&$p) => unset($p['rules'][0]['day'])),
        'El campo día es obligatorio.',
    ],
    'day must be enum' => [
        fn (array $payload): array => tap($payload, fn (&$p) => $p['rules'][0]['day'] = 'invalid-day'),
        'El campo día no está en la lista de valores permitidos.',
    ],
    'missing items' => [
        fn (array $payload): array => tap($payload, fn (&$p) => unset($p['rules'][0]['items'])),
        'El campo elementos es obligatorio.',
    ],
    'items must be array' => [
        fn (array $payload): array => tap($payload, fn (&$p) => $p['rules'][0]['items'] = 'invalid'),
        'El campo elementos debe ser un conjunto.',
    ],
    'items must have at least one item' => [
        fn (array $payload): array => tap($payload, fn (&$p) => $p['rules'][0]['items'] = []),
        'El campo elementos debe tener al menos 1 elementos.',
    ],
    'missing play_time_minutes' => [
        fn (array $payload): array => tap($payload, fn (&$p) => unset($p['rules'][0]['items'][0]['play_time_minutes'])),
        'El campo duración de juego es obligatorio.',
    ],
    'play_time_minutes must be enum' => [
        fn (array $payload): array => tap($payload, fn (&$p) => $p['rules'][0]['items'][0]['play_time_minutes'] = 61),
        'El campo duración de juego no está en la lista de valores permitidos.',
    ],
    'missing prices array' => [
        fn (array $payload): array => tap($payload, fn (&$p) => unset($p['rules'][0]['items'][0]['prices'])),
        'El campo precios es obligatorio.',
    ],
    'prices must be array' => [
        fn (array $payload): array => tap($payload, fn (&$p) => $p['rules'][0]['items'][0]['prices'] = 'invalid'),
        'El campo precios debe ser un conjunto.',
    ],
    'prices must have at least one item' => [
        fn (array $payload): array => tap($payload, fn (&$p) => $p['rules'][0]['items'][0]['prices'] = []),
        'El campo precios debe tener al menos 1 elementos.',
    ],
    'missing starts_at' => [
        fn (array $payload): array => tap($payload, fn (&$p) => unset($p['rules'][0]['items'][0]['prices'][0]['starts_at'])),
        'El campo hora de inicio es obligatorio.',
    ],
    'starts_at format must be H:i' => [
        fn (array $payload): array => tap($payload, fn (&$p) => $p['rules'][0]['items'][0]['prices'][0]['starts_at'] = '9:00'),
        'El campo hora de inicio debe coincidir con el formato H:i.',
    ],
    'missing price' => [
        fn (array $payload): array => tap($payload, fn (&$p) => unset($p['rules'][0]['items'][0]['prices'][0]['price'])),
        'El campo precio es obligatorio.',
    ],
    'price must be numeric' => [
        fn (array $payload): array => tap($payload, fn (&$p) => $p['rules'][0]['items'][0]['prices'][0]['price'] = 'invalid'),
        'El campo precio debe ser numérico.',
    ],
    'price must be at least zero' => [
        fn (array $payload): array => tap($payload, fn (&$p) => $p['rules'][0]['items'][0]['prices'][0]['price'] = -1),
        'El tamaño de precio debe ser de al menos 0.',
    ],
]);

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
        'day' => 'base',
    ]);

    $this->assertDatabaseHas('court_price_rules', [
        'court_id' => $court->id,
        'day' => 'monday',
    ]);

    $baseRule = CourtPriceRule::query()
        ->where('court_id', $court->id)
        ->where('day', 'base')
        ->firstOrFail();

    $this->assertDatabaseHas('court_price_rule_items', [
        'court_price_rule_id' => $baseRule->id,
        'play_time_minutes' => 60,
        'price_starts_at' => '12:00:00',
        'price' => 1200,
    ]);
});

it('shows price rules with play time labels in prices', function (): void {
    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->create();

    $court = Court::factory()->create([
        'club_id' => $club->id,
        'sport_type_id' => $sportType->id,
    ]);

    $baseRule = CourtPriceRule::factory()->forCourt($court)->base()->create();

    CourtPriceRuleItem::factory()
        ->forRule($baseRule)
        ->count(6)
        ->state(new Sequence(
            ['play_time_minutes' => 60, 'price_starts_at' => '09:00:00', 'price' => 3000],
            ['play_time_minutes' => 90, 'price_starts_at' => '09:00:00', 'price' => 4500],
            ['play_time_minutes' => 60, 'price_starts_at' => '12:00:00', 'price' => 4000],
            ['play_time_minutes' => 90, 'price_starts_at' => '12:00:00', 'price' => 5200],
            ['play_time_minutes' => 60, 'price_starts_at' => '18:00:00', 'price' => 5000],
            ['play_time_minutes' => 90, 'price_starts_at' => '18:00:00', 'price' => 6500],
        ))
        ->create();

    $mondayRule = CourtPriceRule::factory()->forCourt($court)->forDay('monday')->create();

    CourtPriceRuleItem::factory()
        ->forRule($mondayRule)
        ->count(2)
        ->state(new Sequence(
            ['play_time_minutes' => 60, 'price_starts_at' => '00:00:00', 'price' => 2800],
            ['play_time_minutes' => 90, 'price_starts_at' => '00:00:00', 'price' => 4200],
        ))
        ->create();

    get(action([CourtPriceRuleController::class, 'show'], ['club' => $club, 'court' => $court]))
        ->assertOk()
        ->assertExactJson([
            'court_id' => $court->id,
            'play_time' => [60, 90],
            'price_starts_at' => ['00:00', '09:00', '12:00', '18:00'],
            'days' => [
                [
                    'day' => 'base',
                    'label' => 'Por defecto',
                    'time_slots' => [
                        [
                            'label' => 'Desde 09:00:00',
                            'starts_at' => '09:00:00',
                            'prices' => [
                                '60 min' => '3000.00',
                                '90 min' => '4500.00',
                            ],
                        ],
                        [
                            'label' => 'Desde 12:00:00',
                            'starts_at' => '12:00:00',
                            'prices' => [
                                '60 min' => '4000.00',
                                '90 min' => '5200.00',
                            ],
                        ],
                        [
                            'label' => 'Desde 18:00:00',
                            'starts_at' => '18:00:00',
                            'prices' => [
                                '60 min' => '5000.00',
                                '90 min' => '6500.00',
                            ],
                        ],
                    ],
                ],
                [
                    'day' => 'monday',
                    'label' => 'Lunes',
                    'time_slots' => [
                        [
                            'label' => 'Desde 00:00:00',
                            'starts_at' => '00:00:00',
                            'prices' => [
                                '60 min' => '2800.00',
                                '90 min' => '4200.00',
                            ],
                        ],
                    ],
                ],
                [
                    'day' => 'tuesday',
                    'label' => 'Martes',
                    'time_slots' => [],
                ],
                [
                    'day' => 'wednesday',
                    'label' => 'Miércoles',
                    'time_slots' => [],
                ],
                [
                    'day' => 'thursday',
                    'label' => 'Jueves',
                    'time_slots' => [],
                ],
                [
                    'day' => 'friday',
                    'label' => 'Viernes',
                    'time_slots' => [],
                ],
                [
                    'day' => 'saturday',
                    'label' => 'Sábado',
                    'time_slots' => [],
                ],
                [
                    'day' => 'sunday',
                    'label' => 'Domingo',
                    'time_slots' => [],
                ],
                [
                    'day' => 'holiday',
                    'label' => 'Festivo',
                    'time_slots' => [],
                ],
            ],
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

    $oldRule = CourtPriceRule::factory()->forCourt($court)->base()->create();
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
            'messages' => ['El ID de la cancha debe coincidir con la cancha en la URL.'],
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
