<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Requests\App\StoreRegisterAppUserRequest;
use App\Http\Requests\App\VerifyEmailRequest;
use App\Models\AppUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class RegisterAppUserController
{
    /**
     * @throws Throwable
     */
    public function store(StoreRegisterAppUserRequest $request): Response
    {
        DB::transaction(function () use ($request): void {
            /** @var AppUser $appUser */
            $appUser = AppUser::query()->create(
                $request->validated(),
            );

            DB::afterCommit(function () use ($appUser): void {
                $appUser->sendEmailVerificationNotification();
            });
        });

        return new Response(status: 201);
    }

    public function verifyEmail(VerifyEmailRequest $request): RedirectResponse
    {

        /** @var AppUser|null $appUser */
        $appUser = AppUser::query()->find($request->id);

        if ($appUser === null) {
            return redirect(config('app.spa_url').'/#/app/auth/email-verification/fail');
        }

        if (! hash_equals($request->hash, sha1($appUser->getEmailForVerification()))) {
            return redirect(config('app.spa_url').'/#/app/auth/email-verification/fail');
        }

        if ($appUser->hasVerifiedEmail()) {
            return redirect(config('app.spa_url').'/#/app/auth/email-verification/fail');
        }

        $appUser->markEmailAsVerified();

        return redirect(config('app.spa_url').'/#/app/auth/email-verification/success');
    }
}
