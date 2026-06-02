<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

//New Middlewares
use App\Http\Middleware\RedirectIfNotAuthenticated;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\AllowBackendAccess;
use App\Http\Middleware\ProtectForms;
use App\Http\Middleware\VerifyRecaptcha;
use App\Http\Middleware\TrackVisitors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // Alias middleware for Spatie permission package (v6+ uses singular 'Middleware' namespace)
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        $middleware->appendToGroup('auth.backend', [
            RedirectIfNotAuthenticated::class
        ]);

        $middleware->appendToGroup('auth.guest', [
            RedirectIfAuthenticated::class
        ]);      
  
        $middleware->appendToGroup('protect.forms', [
            ProtectForms::class
        ]);   
        $middleware->appendToGroup('recaptcha', [
            VerifyRecaptcha::class
        ]);   
        
        //Global
        $middleware->append([
            \Illuminate\Http\Middleware\HandleCors::class,
            \App\Http\Middleware\TrackVisitors::class,
        ]);        
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
