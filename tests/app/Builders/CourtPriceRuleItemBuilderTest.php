<?php

declare(strict_types=1);

use App\Enums\CourtPriceRuleDay;
use App\Enums\PlayTime;
use App\Models\Court;
use App\Models\CourtPriceRule;
use App\Models\CourtPriceRuleItem;

it('retrieves play times for a court', function (): void {
    $court = Court::factory()->create();

    [$genericRule, $mondayRule] = CourtPriceRule::factory()
        ->for($court)
        ->count(2)
        ->sequence(
            ['day' => CourtPriceRuleDay::Base],
            ['day' => CourtPriceRuleDay::Monday],
        )
        ->create()
        ->all();

    CourtPriceRuleItem::factory()
        ->for($genericRule, 'priceRule')
        ->count(2)
        ->sequence(
            ['play_time_minutes' => PlayTime::SixtyMinutes],
            ['play_time_minutes' => PlayTime::NinetyMinutes],
        )
        ->create();

    CourtPriceRuleItem::factory()
        ->for($mondayRule, 'priceRule')
        ->count(2)
        ->sequence(
            ['play_time_minutes' => PlayTime::SixtyMinutes],
            ['play_time_minutes' => PlayTime::NinetyMinutes],
        )
        ->create();

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
        ->create(['day' => CourtPriceRuleDay::Base]);

    $playTimes = CourtPriceRuleItem::query()
        ->getPlayTimesForCourt($court->id);

    expect($playTimes)->toBe([]);
});

it('returns play times ordered correctly', function (): void {
    $court = Court::factory()->create();

    $rule = CourtPriceRule::factory()
        ->for($court)
        ->create(['day' => CourtPriceRuleDay::Base]);

    CourtPriceRuleItem::factory()
        ->for($rule, 'priceRule')
        ->count(3)
        ->sequence(
            ['play_time_minutes' => PlayTime::NinetyMinutes],
            ['play_time_minutes' => PlayTime::SixtyMinutes],
            ['play_time_minutes' => PlayTime::OneHundredTwentyMinutes],
        )
        ->create();

    $playTimes = CourtPriceRuleItem::query()
        ->getPlayTimesForCourt($court->id);

    expect($playTimes)->toBe([60, 90, 120]);
});

it('retrieves price start times for a court', function (): void {
    $court = Court::factory()->create();

    [$genericRule, $mondayRule] = CourtPriceRule::factory()
        ->for($court)
        ->count(2)
        ->sequence(
            ['day' => CourtPriceRuleDay::Base],
            ['day' => CourtPriceRuleDay::Monday],
        )
        ->create()
        ->all();

    CourtPriceRuleItem::factory()
        ->for($genericRule, 'priceRule')
        ->count(2)
        ->sequence(
            ['price_starts_at' => '08:00:00'],
            ['price_starts_at' => '14:00:00'],
        )
        ->create();

    CourtPriceRuleItem::factory()
        ->for($mondayRule, 'priceRule')
        ->count(2)
        ->sequence(
            ['price_starts_at' => '08:00:00'],
            ['price_starts_at' => '10:00:00'],
        )
        ->create();

    $priceStartTimes = CourtPriceRuleItem::query()
        ->getPriceStartsAtForCourt($court->id);

    expect($priceStartTimes)
        ->toBeArray()
        ->toBe(['08:00:00', '10:00:00', '14:00:00']);
});

it('returns empty array when court has no price start times', function (): void {
    $court = Court::factory()->create();

    CourtPriceRule::factory()
        ->for($court)
        ->create(['day' => CourtPriceRuleDay::Base]);

    $priceStartTimes = CourtPriceRuleItem::query()
        ->getPriceStartsAtForCourt($court->id);

    expect($priceStartTimes)->toBe([]);
});

it('returns price start times ordered correctly', function (): void {
    $court = Court::factory()->create();

    $rule = CourtPriceRule::factory()
        ->for($court)
        ->create(['day' => CourtPriceRuleDay::Base]);

    CourtPriceRuleItem::factory()
        ->for($rule, 'priceRule')
        ->count(3)
        ->sequence(
            ['price_starts_at' => '18:00:00'],
            ['price_starts_at' => '08:00:00'],
            ['price_starts_at' => '14:00:00'],
        )
        ->create();

    $priceStartTimes = CourtPriceRuleItem::query()
        ->getPriceStartsAtForCourt($court->id);

    expect($priceStartTimes)->toBe(['08:00:00', '14:00:00', '18:00:00']);
});

it('deduplicates price start times correctly', function (): void {
    $court = Court::factory()->create();

    [$genericRule, $mondayRule] = CourtPriceRule::factory()
        ->for($court)
        ->count(2)
        ->sequence(
            ['day' => CourtPriceRuleDay::Base],
            ['day' => CourtPriceRuleDay::Monday],
        )
        ->create()
        ->all();

    CourtPriceRuleItem::factory()
        ->for($genericRule, 'priceRule')
        ->create([
            'play_time_minutes' => PlayTime::SixtyMinutes,
            'price_starts_at' => '08:00:00',
        ]);

    CourtPriceRuleItem::factory()
        ->for($mondayRule, 'priceRule')
        ->count(2)
        ->sequence(
            [
                'play_time_minutes' => PlayTime::SixtyMinutes,
                'price_starts_at' => '08:00:00',
            ],
            [
                'play_time_minutes' => PlayTime::NinetyMinutes,
                'price_starts_at' => '08:00:00',
            ],
        )
        ->create();

    $priceStartTimes = CourtPriceRuleItem::query()
        ->getPriceStartsAtForCourt($court->id);

    expect($priceStartTimes)->toBe(['08:00:00']);
});
