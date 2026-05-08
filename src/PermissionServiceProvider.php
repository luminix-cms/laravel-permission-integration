<?php

namespace Luminix\LaravelPermissionIntegration;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Luminix\Backend\Services\ModelFinder;
use Luminix\Frontend\Services\BootService;
use Luminix\LaravelPermissionIntegration\Commands\LuminixApiPermissions;
use Luminix\LaravelPermissionIntegration\Facades\Integration;
use Luminix\LaravelPermissionIntegration\Models\Permission;
use Luminix\LaravelPermissionIntegration\Models\Role;
use Luminix\LaravelPermissionIntegration\Services\IntegrationService;

class PermissionServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->setupPackage();

        Integration::makeLuminixFindModels();
    }

    public function boot()
    {
        Integration::addFrontendConfigurations();
        Integration::setRoleableModelsApis();
    }

    protected function setupPackage()
    {

        $this->app->singleton(IntegrationService::class, function () {
            return new IntegrationService();
        });

        $this->loadJsonTranslationsFrom(__DIR__ . '/../lang');

        $this->commands([
            LuminixApiPermissions::class,
        ]);

        $this->mergeConfigFrom(__DIR__ . '/../config/permission.php', 'luminix.permission');

        $this->publishes([
            __DIR__ . '/../config/permission.php' => config_path('luminix/permission.php'),
        ], 'luminix-config');

    }

}
