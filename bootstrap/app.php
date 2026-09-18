<?php

use App\Http\Middleware\EnsureAccountIsActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'active' => EnsureAccountIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Business-rule violations raised by services (e.g. "already received",
        // insufficient stock) are plain RuntimeExceptions with an Arabic message
        // meant for the user — show it as a flash message instead of a crash page.
        // Symfony's HttpException hierarchy (404, 403, 419, ...) also extends
        // RuntimeException, so it must be excluded here or aborts would redirect
        // back to themselves forever instead of rendering their proper HTTP page.
        $exceptions->render(function (RuntimeException $e, Request $request) {
            if ($e instanceof HttpExceptionInterface || $request->expectsJson() || $request->is('api/*')) {
                return null;
            }

            return back()->withInput()->with('error', $e->getMessage());
        });
    })->create();
