<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Requests\Club\StoreCourtPriceRuleRequest;
use App\Http\Resources\Club\ShowCourtPriceRuleResource;
use App\Models\Club;
use App\Models\Court;
use App\Services\CourtPriceRulesShowBuilderService;
use App\Services\OwnershipVerifierService;
use Gate;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class CourtPriceRuleController
{
    /**
     * @throws Throwable
     */
    public function store(
        StoreCourtPriceRuleRequest $request,
        Club $club,
        Court $court
    ): Response {

        Gate::authorize('update', [$court, $club]);

        DB::transaction(function () use ($court, $request): void {
            $court->priceRules()->delete();

            foreach ($request->rulesPayload() as $ruleData) {
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

        return new Response(status: 201);
    }

    public function show(
        Club $club,
        Court $court,
        CourtPriceRulesShowBuilderService $courtPriceRulesShowBuilder,
    ): ShowCourtPriceRuleResource {
        Gate::authorize('view', [$court, $club]);

        return new ShowCourtPriceRuleResource(
            $courtPriceRulesShowBuilder->handle($court),
        );
    }

    private function ensureCourtBelongsToClub(Club $club, Court $court): void
    {
        if ($court->club_id !== $club->id) {
            abort(404, __('validation.resource_not_found'));
        }
    }
}
