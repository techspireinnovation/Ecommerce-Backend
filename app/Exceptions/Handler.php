<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        //
    }

    public function render($request, Throwable $e)
    {
        if ($request->expectsJson() || $request->is('api/*')) {

            // 404 - Model not found or route not found
            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found',
                ], 404);
            }

            // 422 - Validation error
            if ($e instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }

            // 401 / 403 / other HTTP exceptions
            if ($e instanceof HttpException) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'HTTP error',
                ], $e->getStatusCode());
            }

            // 500 fallback
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : 'Something went wrong',
            ], 500);
        }

        return parent::render($request, $e);
    }
}
