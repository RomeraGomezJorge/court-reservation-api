<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Handle HTTP exceptions
        $exceptions->render(function (HttpExceptionInterface $e) {
            return response()->json([
                'messages' => [$e->getMessage()],
                'code' => $e->getStatusCode(),
            ], $e->getStatusCode());
        });

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
