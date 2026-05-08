<?php

namespace Workbench\App\Tests;

use Luminix\Backend\BackendServiceProvider;
use Luminix\LaravelPermissionIntegration\Models\Permission;
use Luminix\LaravelPermissionIntegration\Models\Role;

class FeatureTestCase extends TestCase
{
    protected function getPackageProviders($app)
    {
        return array_merge(parent::getPackageProviders($app), [
            \Spatie\Permission\PermissionServiceProvider::class,
            BackendServiceProvider::class,
        ]);
    }

    protected function defineEnvironment($app)
    {
        $app->config->set('permission.models.role', Role::class);
        $app->config->set('permission.models.permission', Permission::class);

        $app['config']->set('luminix.backend.models.include', [
            'Workbench\App\Models\User',
        ]);

        $app['config']->set('auth', require __DIR__.'/../../config/auth.ci.php');
    }

    protected function setUp(): void
    {
        BackendServiceProvider::preventEnforcingMorphMap();
        parent::setUp();
    }
}
