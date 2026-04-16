<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsClubUser;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        then: function (): void {
            Route::prefix('api/admin')
                ->middleware('api')
                ->group(base_path('routes/admin_user.php'));

            Route::prefix('api/club')
                ->middleware('api')
                ->group(base_path('routes/club_user.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin_user' => EnsureUserIsAdmin::class,
            'ensure_is_club_user' => EnsureUserIsClubUser::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Handle not found exceptions
        $exceptions->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->json()) {
                return response()->json([
                    'messages' => [__('validation.resource_not_found')],
                    'code' => $e->getStatusCode(),
                ],
                    status: $e->getStatusCode());
            }

            return null;
        });

        // Handle HTTP exceptions
        $exceptions->render(fn (HttpExceptionInterface $e) => response()->json([
            'messages' => [$e->getMessage()],
            'code' => $e->getStatusCode(),
        ], $e->getStatusCode()));

        // Handle validation exceptions
        $exceptions->render(function (ValidationException $e) {
            $messages = $e->validator->getMessageBag()->getMessages();
            $mappedMessages = [];

            foreach ($messages as $message) {
                foreach ($message as $errorMessage) {
                    $mappedMessages[] = $errorMessage;
                }
            }

            return response()->json([
                'messages' => $mappedMessages,
                'code' => 422,
            ], 422);
        });
    })->create();
