<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Requests\Club\StoreRegisterClubRequest;
use App\Http\Requests\Club\VerifyEmailRequest;
use App\Models\Club;
use Symfony\Component\HttpFoundation\Response;

final class RegisterClubController
{
    public function store(StoreRegisterClubRequest $request): Response
    {
        /** @var Club $club */
        $club = Club::query()->create($request->validated());

        $club->sendEmailVerificationNotification();

        return new Response(status: 201);
    }

    public function verifyEmail(VerifyEmailRequest $request)
    {

        /** @var Club|null $club */
        $club = Club::query()->find($request->id);

        if ($club === null) {
            return redirect(config('app.spa_url').'/#/auth/email-verification/unsuccessful');
        }

        if (! hash_equals($request->hash, sha1($club->getEmailForVerification()))) {
            return redirect(config('app.spa_url').'/#/auth/email-verification/unsuccessful');
        }

        if ($club->hasVerifiedEmail()) {
            return redirect(config('app.spa_url').'/#/auth/email-verification/unsuccessful');
        }

        $club->markEmailAsVerified();

        return redirect(config('app.spa_url').'/#/auth/email-verification/successful');
    }
}
