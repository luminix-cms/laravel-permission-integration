<?php

namespace Workbench\App\Tests\Feature;

use Luminix\Backend\Services\ModelFinder;
use Luminix\LaravelPermissionIntegration\Commands\LuminixApiPermissions;
use Luminix\LaravelPermissionIntegration\Facades\Integration;
use Luminix\LaravelPermissionIntegration\Models\Permission;
use Luminix\LaravelPermissionIntegration\Models\Role;
use Luminix\LaravelPermissionIntegration\Services\IntegrationService;
use Workbench\App\Tests\FeatureTestCase;

class ServiceProviderTest extends FeatureTestCase
{
    public function test_binds_integration_service_in_container(): void
    {
        $service = app(IntegrationService::class);

        $this->assertInstanceOf(IntegrationService::class, $service);
    }

    public function test_integration_facade_resolves_correctly(): void
    {
        config(['auth.guards' => [
            'web' => ['driver' => 'session'],
        ]]);

        $guards = Integration::getAvailableGuards();

        $this->assertIsArray($guards);
        $this->assertContains('web', $guards);
    }

    public function test_role_model_registered_in_model_finder(): void
    {
        $finder = app(ModelFinder::class);
        $models = $finder->all();

        $this->assertArrayHasKey('role', $models->toArray());
        $this->assertSame(Role::class, $models->get('role'));
    }

    public function test_permission_model_registered_in_model_finder(): void
    {
        $finder = app(ModelFinder::class);
        $models = $finder->all();

        $this->assertArrayHasKey('permission', $models->toArray());
        $this->assertSame(Permission::class, $models->get('permission'));
    }

    public function test_integration_service_returns_available_guards(): void
    {
        config(['auth.guards' => [
            'web' => ['driver' => 'session'],
            'api' => ['driver' => 'token'],
        ]]);

        $service = app(IntegrationService::class);

        $this->assertContains('web', $service->getAvailableGuards());
        $this->assertContains('api', $service->getAvailableGuards());
    }

    public function test_integration_service_is_singleton(): void
    {
        $instance1 = app(IntegrationService::class);
        $instance2 = app(IntegrationService::class);

        $this->assertSame($instance1, $instance2);
    }

    public function test_luminix_api_permissions_command_is_registered(): void
    {
        $this->artisan('luminix:api-permissions', ['--guard' => ['web']])
            ->assertExitCode(0);
    }

    public function test_luminix_permission_config_defaults_to_set_roles(): void
    {
        $this->assertSame('set-roles', config('luminix.permission.permission_to_set_roles'));
    }

    public function test_luminix_permission_config_can_be_overridden(): void
    {
        config(['luminix.permission.permission_to_set_roles' => null]);

        $this->assertNull(config('luminix.permission.permission_to_set_roles'));
    }

    public function test_integration_facade_exposes_make_luminix_find_models(): void
    {
        $this->expectNotToPerformAssertions();
        Integration::makeLuminixFindModels();
    }

    public function test_integration_facade_exposes_add_frontend_configurations(): void
    {
        $this->expectNotToPerformAssertions();
        Integration::addFrontendConfigurations();
    }
}
