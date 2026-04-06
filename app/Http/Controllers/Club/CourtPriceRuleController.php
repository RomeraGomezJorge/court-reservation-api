<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Requests\Club\StoreCourtPriceRuleRequest;
use App\Http\Resources\Club\ShowCourtPriceRuleResource;
use App\Models\Club;
use App\Models\Court;
use App\Services\CourtPriceRulesCreatorService;
use App\Services\CourtPriceRulesShowBuilderService;
use App\Services\OwnershipVerifierService;
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
        Court $court,
        OwnershipVerifierService $ownershipVerifier,
        CourtPriceRulesCreatorService $courtPriceRulesCreator,
    ): Response {
        $this->ensureCourtBelongsToClub($club, $court);
        $ownershipVerifier->handle($court->club);

        $courtPriceRulesCreator->handle($court, $request->rulesPayload());

        return new Response(status: 201);
    }

    public function show(
        Club $club,
        Court $court,
        OwnershipVerifierService $ownershipVerifier,
        CourtPriceRulesShowBuilderService $courtPriceRulesShowBuilder,
    ): ShowCourtPriceRuleResource {
        $this->ensureCourtBelongsToClub($club, $court);
        $ownershipVerifier->handle($court->club);

        return new ShowCourtPriceRuleResource($courtPriceRulesShowBuilder->handle($court));
    }

    private function ensureCourtBelongsToClub(Club $club, Court $court): void
    {
        if ($court->club_id !== $club->id) {
            abort(404, __('validation.resource_not_found', ['resource' => 'Court']));
        }
    }
}
