<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AppUser;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;

final class ClubUserCreateOrAttachAppUserService
{
    private const string POSTGRES_UNIQUE_VIOLATION_SQLSTATE = '23505';

    private const string MYSQL_INTEGRITY_CONSTRAINT_VIOLATION_SQLSTATE = '23000';

    private const int MYSQL_DUPLICATE_ENTRY_ERROR_CODE = 1062;

    /**
     * @param  array{
     *  name:string,
     *  last_name:string,
     *  phone_number:string,
     *  birthday:string,
     *  gender:string,
     *  email:string,
     *  club_ids: array<int,int>
     *  }  $attributes
     *
     * @throws Throwable
     */
    public function handle(array $attributes): AppUser
    {
        return DB::transaction(function () use ($attributes): AppUser {

            $appUser = AppUser::query()
                ->where('email', $attributes['email'])
                ->first();

            if ($appUser) {
                $appUser->clubs()->syncWithoutDetaching($attributes['club_ids']);

                return $appUser;
            }

            $appUser = $this->createAppUser($attributes);

            $appUser->clubs()->syncWithoutDetaching($attributes['club_ids']);

            DB::afterCommit(function () use ($appUser): void {
                Password::broker('app_users')->sendResetLink(['email' => $appUser->email]);
            });

            return $appUser;
        });
    }

    /**
     * @param  array{
     *  name:string,
     *  last_name:string,
     *  phone_number:string,
     *  birthday:string,
     *  gender:string,
     *  email:string,
     *  club_ids: array<int,int>
     *  }  $attributes
     */
    private function createAppUser(array $attributes): AppUser
    {
        $values = $attributes;
        unset($values['club_ids']);

        try {
            $appUser = AppUser::query()->create([
                ...$values,
                'password' => Hash::make(Str::password()),
            ]);

            $appUser->markEmailAsVerified();

            return $appUser;

        } catch (QueryException $queryException) {
            if (! $this->isUniqueConstraintViolation($queryException)) {
                throw $queryException;
            }

            Log::warning('Duplicate app user creation avoided', [
                'email' => $attributes['email'],
            ]);

            return AppUser::query()->where('email', $attributes['email'])->firstOrFail();
        }
    }

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? null;

        $driverCode = $exception->errorInfo[1] ?? null;

        return match ($sqlState) {
            self::POSTGRES_UNIQUE_VIOLATION_SQLSTATE => true,
            self::MYSQL_INTEGRITY_CONSTRAINT_VIOLATION_SQLSTATE => $driverCode === self::MYSQL_DUPLICATE_ENTRY_ERROR_CODE,
            default => false,
        };
    }
}
