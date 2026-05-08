<?php

namespace Workbench\App\Tests\Feature;

use Luminix\LaravelPermissionIntegration\Models\Permission;
use Workbench\App\Tests\FeatureTestCase;

class LuminixApiPermissionsTest extends FeatureTestCase
{
    public function test_creates_crud_permissions_for_all_finder_models(): void
    {
        config(['auth.guards' => [
            'web' => ['driver' => 'session', 'provider' => 'users'],
        ]]);

        $this->artisan('luminix:api-permissions', ['--guard' => ['web']])
            ->assertExitCode(0);

        $names = Permission::where('guard_name', 'web')->pluck('name')->toArray();

        foreach (['role', 'permission'] as $model) {
            foreach (['create', 'read', 'update', 'delete'] as $operation) {
                $this->assertContains("{$operation}-{$model}", $names);
            }
        }
    }

    public function test_creates_permissions_only_for_specified_guard(): void
    {
        config(['auth.guards' => [
            'web' => ['driver' => 'session'],
            'api' => ['driver' => 'token'],
        ]]);

        $this->artisan('luminix:api-permissions', ['--guard' => ['api']])
            ->assertExitCode(0);

        $this->assertNotEmpty(Permission::where('guard_name', 'api')->get());
        $this->assertEmpty(Permission::where('guard_name', 'web')->get());
    }

    public function test_creates_permissions_for_multiple_guards(): void
    {
        config(['auth.guards' => [
            'web' => ['driver' => 'session'],
            'api' => ['driver' => 'token'],
        ]]);

        $this->artisan('luminix:api-permissions', ['--guard' => ['web', 'api']])
            ->assertExitCode(0);

        $this->assertNotEmpty(Permission::where('guard_name', 'web')->get());
        $this->assertNotEmpty(Permission::where('guard_name', 'api')->get());
    }

    public function test_command_is_idempotent(): void
    {
        config(['auth.guards' => [
            'web' => ['driver' => 'session'],
        ]]);

        $this->artisan('luminix:api-permissions', ['--guard' => ['web']])->assertExitCode(0);
        $countAfterFirst = Permission::count();

        $this->artisan('luminix:api-permissions', ['--guard' => ['web']])->assertExitCode(0);
        $countAfterSecond = Permission::count();

        $this->assertSame($countAfterFirst, $countAfterSecond);
    }

    public function test_creates_four_operations_per_model_per_guard(): void
    {
        config(['auth.guards' => [
            'web' => ['driver' => 'session'],
        ]]);

        $modelsInFinder = 3; // user + role + permission
        $operations = 4;     // create, read, update, delete

        $this->artisan('luminix:api-permissions', ['--guard' => ['web']])->assertExitCode(0);

        $this->assertSame(
            $modelsInFinder * $operations,
            Permission::where('guard_name', 'web')->count()
        );
    }
}
