<?php

namespace App\Providers;

use App\Contracts\PageBuilder\WidgetInterface;
use App\Services\PageBuilder\Core\WidgetManager;
use App\Services\PageBuilder\Core\ElementManager;
use App\Services\PageBuilder\Core\Renderer;
use App\Services\PageBuilder\Core\PageBuilderService;
use App\Services\PageBuilder\Core\TemplateManager;
use App\Services\PageBuilder\Core\CollaborationService;
use App\Services\PageBuilder\GlobalWidget\GlobalWidgetService;
use Illuminate\Support\ServiceProvider;

class PageBuilderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            config_path('page-builder.php'), 'page-builder'
        );

        $this->app->singleton(WidgetManager::class, function ($app) {
            $manager = new WidgetManager();
            $manager->registerFromConfig();
            return $manager;
        });

        $this->app->singleton(ElementManager::class, function ($app) {
            return new ElementManager(
                $app->make(WidgetManager::class)
            );
        });

        $this->app->singleton(Renderer::class, function ($app) {
            return new Renderer(
                $app->make(WidgetManager::class)
            );
        });

        $this->app->singleton(PageBuilderService::class, function ($app) {
            return new PageBuilderService(
                $app->make(WidgetManager::class),
                $app->make(ElementManager::class),
                $app->make(Renderer::class)
            );
        });

        $this->app->singleton(TemplateManager::class, function ($app) {
            return new TemplateManager();
        });

        $this->app->singleton(CollaborationService::class, function ($app) {
            return new CollaborationService();
        });


        $this->app->singleton(GlobalWidgetService::class, function ($app) {
            return new GlobalWidgetService(
                $app->make(Renderer::class),
                $app->make(WidgetManager::class)
            );
        });

        $this->app->alias(PageBuilderService::class, 'page-builder');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(base_path('routes/page-builder.php'));

        $this->loadViewsFrom(resource_path('views/page-builder'), 'page-builder');

        $this->publishes([
            config_path('page-builder.php') => config_path('page-builder.php'),
        ], 'page-builder-config');

        $this->publishes([
            resource_path('views/page-builder') => resource_path('views/vendor/page-builder'),
        ], 'page-builder-views');

        $this->publishes([
            database_path('migrations') => database_path('migrations'),
        ], 'page-builder-migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([

            ]);
        }
    }
}
