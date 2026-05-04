<?php

namespace Workbench\App\Tests\Unit;

use Luminix\LaravelPermissionIntegration\Services\IntegrationService;
use Workbench\App\Tests\TestCase;

class IntegrationServiceTest extends TestCase
{
    public function test_returns_all_configured_guard_names(): void
    {
        config(['auth.guards' => [
            'web' => ['driver' => 'session', 'provider' => 'users'],
            'api' => ['driver' => 'token', 'provider' => 'users'],
        ]]);

        $service = new IntegrationService();

        $this->assertSame(['web', 'api'], $service->getAvailableGuards());
    }

    public function test_returns_empty_array_when_no_guards_configured(): void
    {
        config(['auth.guards' => []]);

        $service = new IntegrationService();

        $this->assertEmpty($service->getAvailableGuards());
    }

    public function test_returns_keys_only_not_driver_config(): void
    {
        config(['auth.guards' => [
            'web' => ['driver' => 'session', 'provider' => 'users'],
        ]]);

        $service = new IntegrationService();
        $guards = $service->getAvailableGuards();

        $this->assertSame(['web'], $guards);
        $this->assertNotContains('session', $guards);
    }

    public function test_returns_single_guard(): void
    {
        config(['auth.guards' => [
            'sanctum' => ['driver' => 'sanctum'],
        ]]);

        $service = new IntegrationService();

        $this->assertSame(['sanctum'], $service->getAvailableGuards());
    }
}
