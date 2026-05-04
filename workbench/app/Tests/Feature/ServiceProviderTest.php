<?php

namespace Workbench\App\Tests\Feature;

use Luminix\Backend\Services\ModelFinder;
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
}
