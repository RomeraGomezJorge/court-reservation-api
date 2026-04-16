<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Club;

use App\Http\Controllers\Club\CourtController;
use App\Models\Club;
use App\Models\ClubUser;
use App\Models\Court;
use App\Models\Feature;
use App\Models\SportType;

use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

function validCourtPayload(array $overrides = []): array
{
    $sportType = SportType::factory()->create();
    $features = Feature::factory()->count(2)->create();

    return array_merge([
        'name' => 'Cancha Central',
        'description' => 'Cancha para partidos oficiales',
        'sport_type_id' => $sportType->id,
        'features' => [$features[0]->id, $features[1]->id],
    ], $overrides);
}

beforeEach(function (): void {
    $this->clubUser = actingAsClubUser();
});

it('stores a court with default availability in true', function (): void {
    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
    ]);

    $payload = validCourtPayload([
        'name' => 'Cancha Norte',
    ]);

    post(action([CourtController::class, 'store'], ['club' => $club]), $payload)
        ->assertStatus(201);

    $court = Court::query()->where('name', 'Cancha Norte')->firstOrFail();

    $this->assertDatabaseHas('courts', [
        'id' => $court->id,
        'club_id' => $club->id,
        'name' => 'Cancha Norte',
        'is_available' => true,
    ]);

    $this->assertDatabaseHas('court_feature', [
        'court_id' => $court->id,
        'feature_id' => $payload['features'][0],
    ]);

    $this->assertDatabaseHas('court_feature', [
        'court_id' => $court->id,
        'feature_id' => $payload['features'][1],
    ]);
});

it('fails to store a court with duplicate name inside the same club', function (): void {
    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->create();

    Court::factory()->create([
        'club_id' => $club->id,
        'sport_type_id' => $sportType->id,
        'name' => 'Cancha Repetida',
    ]);

    post(action([CourtController::class, 'store'], ['club' => $club]), [
        'name' => 'Cancha Repetida',
        'description' => 'Descripcion',
        'sport_type_id' => $sportType->id,
        'features' => [],
    ])->assertExactJson([
        'code' => 422,
        'messages' => ['El campo nombre ya ha sido registrado.'],
    ]);
});

it('fails to store a court when the club is not owned by authenticated user', function (): void {
    $otherClub = Club::factory()->create();

    post(action([CourtController::class, 'store'], ['club' => $otherClub]), validCourtPayload())
        ->assertNotFound()
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('shows a court', function (): void {
    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->create([
        'name' => 'Padel',
    ]);

    $court = Court::factory()->create([
        'club_id' => $club->id,
        'sport_type_id' => $sportType->id,
        'name' => 'Cancha Show',
        'description' => 'Descripcion show',
        'is_available' => true,
    ]);

    [$featureOne, $featureTwo] = Feature::factory()
        ->count(2)
        ->sequence(
            ['name' => 'Iluminacion'],
            ['name' => 'Cesped sintetico'],
        )
        ->create();

    $court->features()->sync([$featureOne->id, $featureTwo->id]);

    get(action([CourtController::class, 'show'], ['club' => $club, 'court' => $court]))
        ->assertOk()
        ->assertExactJson([
            'id' => $court->id,
            'club_id' => $club->id,
            'sport_type_id' => $sportType->id,
            'name' => 'Cancha Show',
            'description' => 'Descripcion show',
            'is_available' => true,
            'sport_type' => [
                'id' => $sportType->id,
                'name' => 'Padel',
            ],
            'features' => [
                ['id' => $featureOne->id, 'name' => 'Iluminacion'],
                ['id' => $featureTwo->id, 'name' => 'Cesped sintetico'],
            ],
        ]);
});

it('fails to show a court when the court does not belong to the club in route', function (): void {
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

    get(action([CourtController::class, 'show'], ['club' => $ownedClub, 'court' => $court]))
        ->assertNotFound()
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('fails to show a court when the club is not owned by authenticated user', function (): void {
    $otherClub = Club::factory()->create();
    $sportType = SportType::factory()->create();

    $court = Court::factory()->create([
        'club_id' => $otherClub->id,
        'sport_type_id' => $sportType->id,
    ]);

    get(action([CourtController::class, 'show'], ['club' => $otherClub, 'court' => $court]))
        ->assertNotFound()
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('updates a court and syncs features', function (): void {
    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->create();
    $newSportType = SportType::factory()->create();

    $court = Court::factory()->create([
        'club_id' => $club->id,
        'sport_type_id' => $sportType->id,
        'name' => 'Cancha Base',
    ]);

    [$oldFeature] = Feature::factory()->count(1)->create();
    [$newFeatureOne, $newFeatureTwo] = Feature::factory()->count(2)->create();

    $court->features()->sync([$oldFeature->id]);

    put(action([CourtController::class, 'update'], ['club' => $club, 'court' => $court]), [
        'name' => 'Cancha Actualizada',
        'description' => 'Descripcion actualizada',
        'sport_type_id' => $newSportType->id,
        'features' => [$newFeatureOne->id, $newFeatureTwo->id],
    ])->assertNoContent();

    $this->assertDatabaseHas('courts', [
        'id' => $court->id,
        'name' => 'Cancha Actualizada',
        'sport_type_id' => $newSportType->id,
    ]);

    $this->assertDatabaseMissing('court_feature', [
        'court_id' => $court->id,
        'feature_id' => $oldFeature->id,
    ]);

    $this->assertDatabaseHas('court_feature', [
        'court_id' => $court->id,
        'feature_id' => $newFeatureOne->id,
    ]);

    $this->assertDatabaseHas('court_feature', [
        'court_id' => $court->id,
        'feature_id' => $newFeatureTwo->id,
    ]);
});

it('fails to update a court when the court does not belong to the club in route', function (): void {
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
        'name' => 'Cancha Inalterable',
    ]);

    put(action([CourtController::class, 'update'], ['club' => $ownedClub, 'court' => $court]), [
        'name' => 'Cancha Actualizada',
        'description' => 'Descripcion actualizada',
        'sport_type_id' => $sportType->id,
        'features' => [],
    ])->assertNotFound()
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);

    $this->assertDatabaseHas('courts', [
        'id' => $court->id,
        'name' => 'Cancha Inalterable',
    ]);
});

it('fails to update a court when the club is not owned by authenticated user', function (): void {
    $otherClub = Club::factory()->create();
    $sportType = SportType::factory()->create();

    $court = Court::factory()->create([
        'club_id' => $otherClub->id,
        'sport_type_id' => $sportType->id,
        'name' => 'Cancha Inalterable',
    ]);

    put(action([CourtController::class, 'update'], ['club' => $otherClub, 'court' => $court]), [
        'name' => 'Cancha Actualizada',
        'description' => 'Descripcion actualizada',
        'sport_type_id' => $sportType->id,
        'features' => [],
    ])->assertNotFound()
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);

    $this->assertDatabaseHas('courts', [
        'id' => $court->id,
        'name' => 'Cancha Inalterable',
    ]);
});

it('updates a court without features payload keeping existing ones', function (): void {
    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->create();
    $feature = Feature::factory()->create();

    $court = Court::factory()->create([
        'club_id' => $club->id,
        'sport_type_id' => $sportType->id,
    ]);

    $court->features()->sync([$feature->id]);

    put(action([CourtController::class, 'update'], ['club' => $club, 'court' => $court]), [
        'name' => 'Cancha sin cambiar features',
        'description' => 'Descripcion',
        'sport_type_id' => $sportType->id,
    ])->assertNoContent();

    $this->assertDatabaseHas('court_feature', [
        'court_id' => $court->id,
        'feature_id' => $feature->id,
    ]);
});

it('deletes a court with soft delete and renames it to keep unique constraints available', function (): void {
    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->create();

    $court = Court::factory()->create([
        'club_id' => $club->id,
        'sport_type_id' => $sportType->id,
        'name' => 'Cancha Eliminada',
    ]);

    delete(action([CourtController::class, 'destroy'], ['club' => $club, 'court' => $court]))
        ->assertNoContent();

    $court->refresh();

    $this->assertSoftDeleted($court);
    expect($court->name)->toBe("Cancha Eliminada (deleted #{$court->id})");
});

it('fails to delete a court when the court does not belong to the club in route', function (): void {
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
        'name' => 'Cancha Sin Eliminar',
    ]);

    delete(action([CourtController::class, 'destroy'], ['club' => $ownedClub, 'court' => $court]))
        ->assertNotFound()
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);

    $this->assertNotSoftDeleted($court);
});

it('fails to delete a court when the club is not owned by authenticated user', function (): void {
    $club = Club::factory()->create(
        ['club_user_id' => $this->clubUser->id]
    );

    $otherClubUser = ClubUser::factory()->create();
    $otherClub = Club::factory()->create([
        'club_user_id' => $otherClubUser->id,
    ]);

    $sportType = SportType::factory()->create();

    $court = Court::factory()->create([
        'club_id' => $otherClub->id,
        'sport_type_id' => $sportType->id,
        'name' => 'Cancha Sin Eliminar',
    ]);

    delete(action([CourtController::class, 'destroy'], ['club' => $club, 'court' => $court]))
        ->assertNotFound()
        ->assertExactJson([
            'code' => 404,

            'messages' => ['El recurso no se ha encontrado.'],
        ]);

    $this->assertNotSoftDeleted($court);
});
