<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Unified API error shape (ARCHITECTURE §4): { "error": { "code", "message" } }
        $exceptions->shouldRenderJsonWhen(
            fn ($request) => $request->is('api/*') || $request->expectsJson()
        );

        $exceptions->render(function (Throwable $e, $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'error' => ['code' => 'VALIDATION_ERROR', 'message' => $e->message()],
                ], 422);
            }

            if ($e instanceof NotFoundHttpException
                || $e instanceof ModelNotFoundException) {
                return response()->json([
                    'error' => ['code' => 'NOT_FOUND', 'message' => 'Resource not found.'],
                ], 404);
            }

            if ($e instanceof HttpException) {
                return response()->json([
                    'error' => ['code' => 'HTTP_ERROR', 'message' => $e->getMessage() !== '' ? $e->getMessage() : 'Request failed.'],
                ], $e->getStatusCode());
            }

            report($e);

            return response()->json([
                'error' => ['code' => 'INTERNAL', 'message' => 'Internal error. Check application logs.'],
            ], 500);
        });
    })->create();
