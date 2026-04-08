<?php

declare(strict_types=1);

use App\Enums\PlayTime;
use App\Enums\WorkingDays;
use App\Models\Court;
use App\Models\CourtPriceRule;
use App\Models\CourtPriceRuleItem;


it('retrieves unique play times for a court', function (): void {
    $court = Court::factory()->create();

    $genericRule = CourtPriceRule::factory()
        ->for($court)
        ->create(['day' => null]);

    $mondayRule = CourtPriceRule::factory()
        ->for($court)
        ->create(['day' => WorkingDays::Monday]);

    CourtPriceRuleItem::factory()
        ->for($genericRule, 'priceRule')
        ->create(['play_time_minutes' => PlayTime::SixtyMinutes]);

    CourtPriceRuleItem::factory()
        ->for($genericRule, 'priceRule')
        ->create(['play_time_minutes' => PlayTime::NinetyMinutes]);

    CourtPriceRuleItem::factory()
        ->for($mondayRule, 'priceRule')
        ->create(['play_time_minutes' => PlayTime::SixtyMinutes]);

    CourtPriceRuleItem::factory()
        ->for($mondayRule, 'priceRule')
        ->create(['play_time_minutes' => PlayTime::NinetyMinutes->value]);

    $playTimes = CourtPriceRuleItem::query()
        ->getPlayTimesForCourt($court->id);

    expect($playTimes)
        ->toBeArray()
        ->toBe([60, 90]);
});

it('returns empty array when court has no price rule items', function (): void {
    $court = Court::factory()->create();

    CourtPriceRule::factory()
        ->for($court)
        ->create(['day' => null]);

    $playTimes = CourtPriceRuleItem::query()
        ->getPlayTimesForCourt($court->id);

    expect($playTimes)->toBe([]);
});

it('returns play times ordered correctly', function (): void {
    $court = Court::factory()->create();

    $rule = CourtPriceRule::factory()
        ->for($court)
        ->create(['day' => null]);

    CourtPriceRuleItem::factory()
        ->for($rule, 'priceRule')
        ->create(['play_time_minutes' => PlayTime::NinetyMinutes]);

    CourtPriceRuleItem::factory()
        ->for($rule, 'priceRule')
        ->create(['play_time_minutes' => PlayTime::SixtyMinutes]);

    CourtPriceRuleItem::factory()
        ->for($rule, 'priceRule')
        ->create(['play_time_minutes' => PlayTime::OneHundredTwentyMinutes]);

    $playTimes = CourtPriceRuleItem::query()
        ->getPlayTimesForCourt($court->id);

    expect($playTimes)->toBe([60, 90, 120]);
});

