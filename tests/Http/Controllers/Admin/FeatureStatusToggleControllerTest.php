<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Admin;

use App\Http\Controllers\Admin\FeatureStatusToggleController;
use App\Models\Feature;

use function Pest\Laravel\patch;

beforeEach(function (): void {
    $this->user = actingAsUser();
});

it('toggles feature status', function (): void {
    $feature = Feature::factory()->create([
        'is_active' => true,
    ]);

    patch(action([FeatureStatusToggleController::class], $feature))
        ->assertNoContent();

    $feature->refresh();
    expect($feature->is_active)->toBeFalse();

    patch(action([FeatureStatusToggleController::class], $feature))
        ->assertNoContent();

    $feature->refresh();
    expect($feature->is_active)->toBeTrue();
});

it('fails to toggle a feature status that does not exist', function (): void {
    patch(action([FeatureStatusToggleController::class], 999))
        ->assertNotFound()
        ->assertExactJson([
            'code' => 404,
            'messages' => ['No query results for model [App\\Models\\Feature] 999'],
        ]);
});
