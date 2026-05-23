<?php

use App\Exceptions\InsufficientBalanceException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(HandleCors::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Dados inválidos.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (InsufficientBalanceException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        });

        // Fallback para exceções inesperadas — exclui as que o Laravel já trata nativamente
        // (AuthenticationException → 401, HttpException → status próprio)
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (
                $request->expectsJson()
                && !($e instanceof AuthenticationException)
                && !($e instanceof HttpExceptionInterface)
            ) {
                return response()->json(['message' => 'Erro interno do servidor.'], 500);
            }
        });
    })->create();
