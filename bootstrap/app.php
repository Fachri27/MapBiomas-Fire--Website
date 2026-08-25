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
    ->withMiddleware(function (Middleware $middleware) {
        // Produksi berada di belakang proxy yang mengakhiri TLS. Tanpa ini
        // Laravel mengira permintaan datang lewat http, jadi URL bertanda
        // tangan untuk unggahan Livewire terbit sebagai http:// dan ditolak
        // browser (unggahan PDF menggantung / 405).
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
