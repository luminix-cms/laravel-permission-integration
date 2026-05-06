<?php

namespace Luminix\LaravelPermissionIntegration;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Luminix\Backend\Services\ModelFinder;
use Luminix\Frontend\Services\BootService;
use Luminix\LaravelPermissionIntegration\Facades\Integration;
use Luminix\LaravelPermissionIntegration\Models\Permission;
use Luminix\LaravelPermissionIntegration\Models\Role;
use Luminix\LaravelPermissionIntegration\Services\IntegrationService;

class PermissionServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->bindPackageServices();
        $this->makeLuminixFindModels();
        $this->loadTranslations();
    }

    public function boot()
    {
        $this->addFrontendConfigurations();
    }

    protected function bindPackageServices()
    {
        $this->app->bind(IntegrationService::class, function () {
            return new IntegrationService();
        });

    }

    protected function makeLuminixFindModels()
    {

        $roleClass = config('permission.models.role', Role::class);
        $permissionClass = config('permission.models.permission', Permission::class);

        $namespace = App::getNamespace() . str_replace(
            '/',
            '\\',
            config('luminix.backend.models.directory', 'Models')
        );

        $toAdd = [];

        if (!str_starts_with($roleClass, $namespace)) {
            $toAdd[] = $roleClass;
        }

        if (!str_starts_with($permissionClass, $namespace)) {
            $toAdd[] = $permissionClass;
        }

        if (!empty($toAdd)) {
            ModelFinder::addModels($toAdd);
        }
    }

    protected function loadTranslations()
    {

        $this->loadJsonTranslationsFrom(__DIR__ . '/../lang');

    }

    protected function addFrontendConfigurations()
    {
        BootService::reducer('wireConfig', fn ($config) => [
            ...$config,
            'permission' => [
                ...($config['permission'] ?? []),
                'available_guards' => Integration::getAvailableGuards(),
            ]
        ]);
    }
}
