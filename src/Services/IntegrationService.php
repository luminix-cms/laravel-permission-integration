<?php

namespace Luminix\LaravelPermissionIntegration\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Luminix\Backend\Facades\Finder;
use Luminix\Backend\Services\ModelFinder;
use Luminix\Frontend\Services\BootService;
use Luminix\Frontend\Services\ManifestService;
use Luminix\LaravelPermissionIntegration\Models\Permission;
use Luminix\LaravelPermissionIntegration\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class IntegrationService
{
    public function getAvailableGuards()
    {
        return array_keys(config('auth.guards'));
    }

    public function makeLuminixFindModels()
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

    public function addFrontendConfigurations()
    {

        BootService::reducer('wireConfig', fn ($config) => [
            ...$config,
            'permission' => [
                ...($config['permission'] ?? []),
                'available_guards' => $this->getAvailableGuards(),
            ],
        ]);

    }

    public function setRoleableModelsApis()
    {

        Finder::all()->each(function ($Model) {
            if (in_array(HasRoles::class, class_uses($Model))) {
                $Model::luminixSaving(function ($model) {
                    $requirement = config('luminix.permission.permission_to_set_roles', 'set-roles');
                    if ($requirement && !Gate::check($requirement, [$model])) {
                        return;
                    }

                    if (request()->has('roles')) {
                        $model->syncRoles(...request()->json('roles'));
                    }
                });

                ManifestService::reducer("model" . class_basename($Model) . 'Manifest', function ($manifest) {
                    $manifest['has_roles'] = true;
                    return $manifest;
                });
            }
        });

    }

}
