<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ClubUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use ReflectionClass;
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
            $modelName = $this->getModelName($model);
            $errorMessage = $this->getNotFoundMessage($modelName);
            abort(404, $errorMessage);
        }
    }

    /**
     * Gets the model name from the class, converted to snake_case.
     */
    private function getModelName(Model $model): string
    {
        $reflection = new ReflectionClass($model);

        return Str::snake($reflection->getShortName());
    }

    /**
     * Gets the appropriate not found message for the model.
     * Uses specific translation key if available, falls back to generic message.
     */
    private function getNotFoundMessage(string $modelName): string
    {
        $translationKey = "{$modelName}.not_found";

        if (Lang::has($translationKey)) {
            return __($translationKey);
        }

        $formattedName = ucfirst(str_replace('_', ' ', $modelName));

        return __('validation.resource_not_found', ['resource' => $formattedName]);
    }
}
