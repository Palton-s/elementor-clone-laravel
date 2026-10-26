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
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\InjectThemeTemplates::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\ContentSecurityPolicy::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function ($request, \Throwable $e) {
            return $request->expectsJson() || $request->is('api/*');
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Não encontrado'], 404);
            }
        });

        // Upload bigger than PHP's post_max_size: the request body is dropped before validation runs
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            $limit = ini_get('post_max_size');
            $message = "Arquivo grande demais para o servidor (limite atual do PHP: {$limit}). "
                . 'Aumente upload_max_filesize e post_max_size no php.ini ou envie um arquivo menor.';
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => $message], 413);
            }

            return response($message, 413);
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Você não tem permissão para fazer isso'], 403);
            }
        });
    })->create();
