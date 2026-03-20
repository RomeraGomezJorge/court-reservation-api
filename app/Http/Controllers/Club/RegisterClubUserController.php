<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Club\StoreRegisterClubRequest;
use App\Http\Requests\Club\VerifyEmailRequest;
use App\Models\ClubUser;
use Symfony\Component\HttpFoundation\Response;

final class RegisterClubUserController
{
    public function store(StoreRegisterClubRequest $request): Response
    {
        /** @var ClubUser $clubUser */
        $clubUser = ClubUser::query()->create($request->validated());

        $clubUser->sendEmailVerificationNotification();

        return new Response(status: 201);
    }

    public function verifyEmail(VerifyEmailRequest $request): RedirectResponse
    {

        /** @var ClubUser|null $clubUser */
        $clubUser = ClubUser::query()->find($request->id);

        if ($clubUser === null) {
            return redirect(config('app.spa_url').'/#/club/auth/email-verification/fail');
        }

        if (! hash_equals($request->hash, sha1($clubUser->getEmailForVerification()))) {
            return redirect(config('app.spa_url').'/#/club/auth/email-verification/fail');
        }

        if ($clubUser->hasVerifiedEmail()) {
            return redirect(config('app.spa_url').'/#/club/auth/email-verification/fail');
        }

        $clubUser->markEmailAsVerified();

        return redirect(config('app.spa_url').'/#/club/auth/email-verification/success');
    }
}
