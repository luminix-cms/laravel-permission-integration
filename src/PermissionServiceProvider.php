<?php

namespace Luminix\LaravelPermissionIntegration;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Luminix\Backend\Services\ModelFinder;
use Luminix\LaravelPermissionIntegration\Models\Permission;
use Luminix\LaravelPermissionIntegration\Models\Role;
use Luminix\LaravelPermissionIntegration\Services\IntegrationService;

class PermissionServiceProvider extends ServiceProvider
{
    public function boot()
    {

        $this->makeLuminixFindModels();

    }

    public function register()
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
}
