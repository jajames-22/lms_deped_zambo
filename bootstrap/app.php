<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register your middleware alias here
        $middleware->alias([
            'check.assessment.access' => \App\Http\Middleware\EnsureStudentHasAccess::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\UpdateLastLogin::class, // <-- ADD THIS LINE
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
