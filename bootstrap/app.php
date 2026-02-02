<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // API exception handling
        $exceptions->render(function (Throwable $e, Request $request) {

            // Let Laravel handle web requests
            if (!$request->is('api/*')) {
                return null;
            }

            // Validation error
            if ($e instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }

            // Authentication error
            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            // Model not found
            if ($e instanceof ModelNotFoundException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found',
                ], 404);
            }

            // HTTP exceptions
            if ($e instanceof HttpExceptionInterface) {

                $statusCode = $e->getStatusCode();

                $message = match ($statusCode) {
                    403 => 'Forbidden',
                    404 => 'Resource not found',
                    405 => 'Method not allowed',
                    default => 'HTTP error',
                };

                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], $statusCode);
            }

            // Server error
            return response()->json([
                'success' => false,
                'message' => config('app.debug')
                    ? $e->getMessage()
                    : 'An unexpected error occurred. Please try again later.',
            ], 500);
        });

        // Exception reporting
        $exceptions->report(function (Throwable $e) {
            // Default logging
        });

        // Exceptions not reported
        $exceptions->dontReport([
            ValidationException::class,
            ModelNotFoundException::class,
        ]);
    })
    ->create();

//Helper Function
// function resolveHttpMessage(HttpExceptionInterface $e): string
// {
//     return match ($e->getStatusCode()) {
//         403 => 'Forbidden',
//         404 => 'Resource not found',
//         405 => 'Method not allowed',
//         default => 'HTTP error',
//     };
// }