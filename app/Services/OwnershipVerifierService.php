<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ClubUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

final class OwnershipVerifierService
{
    /**
     * Verifies that the authenticated user is the owner of the model.
     *
     * @param  Model  $model  The model to verify ownership for
     *
     * @throws RuntimeException If the authenticated user is not an instance of AppUser
     */
    public function handle(Model $model): void
    {
        /** @var ClubUser|User $clubUser */
        $clubUser = Auth::user();

        // @codeCoverageIgnoreStart
        if (! $clubUser instanceof ClubUser) {
            throw new RuntimeException('The authenticated user is not an instance of ClubUser');
        }

        // @codeCoverageIgnoreEnd
        if (! $clubUser->owns($model)) {
            abort(404, __('validation.resource_not_found'));
        }
    }
}
