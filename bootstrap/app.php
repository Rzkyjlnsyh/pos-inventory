<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'owner' => \App\Http\Middleware\Owner::class,
            'finance' => \App\Http\Middleware\Finance::class,
            'kepala_toko' => \App\Http\Middleware\KepalaToko::class,
            'admin' => \App\Http\Middleware\Admin::class,
            'editor' => \App\Http\Middleware\Editor::class,
            'karyawan' => \App\Http\Middleware\Karyawan::class,
            'inventaris' => \App\Http\Middleware\Inventaris::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
