<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Court;
use Illuminate\Support\Facades\DB;
use Throwable;

final class CourtPriceRulesCreatorService
{
    /**
     * @param  array<int, array{
     *     day: string|null,
     *     items: array<int, array{
     *         play_time_minutes: int,
     *         prices: array<int, array{starts_at: string, price: int|float|string}>
     *     }>
     * }>  $rules
     *
     * @throws Throwable
     */
    public function handle(Court $court, array $rules): void
    {
        DB::transaction(function () use ($court, $rules): void {
            $court->priceRules()->delete();

            foreach ($rules as $ruleData) {
                $priceRule = $court->priceRules()->create([
                    'day' => $ruleData['day'],
                ]);

                foreach ($ruleData['items'] as $itemData) {
                    foreach ($itemData['prices'] as $priceData) {
                        $priceRule->items()->create([
                            'play_time_minutes' => $itemData['play_time_minutes'],
                            'price_starts_at' => $priceData['starts_at'],
                            'price' => $priceData['price'],
                        ]);
                    }
                }
            }
        });
    }
}
