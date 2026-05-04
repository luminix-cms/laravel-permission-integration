<?php

namespace Workbench\App\Tests\Feature;

use Luminix\LaravelPermissionIntegration\Models\Permission;
use Luminix\LaravelPermissionIntegration\Models\Role;
use Luminix\LaravelPermissionIntegration\Observers\RoleObserver;
use Workbench\App\Tests\FeatureTestCase;

class RoleObserverTest extends FeatureTestCase
{
    private function jsonRequest(array $data): \Illuminate\Http\Request
    {
        return \Illuminate\Http\Request::create(
            '/',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
    }

    public function test_syncs_permissions_when_request_has_permissions_key(): void
    {
        Permission::create(['name' => 'edit-post', 'guard_name' => 'web']);
        Permission::create(['name' => 'delete-post', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);

        $this->app->instance('request', $this->jsonRequest([
            'permissions' => ['edit-post', 'delete-post'],
        ]));

        (new RoleObserver())->luminixSaved($role);

        $role->load('permissions');
        $this->assertCount(2, $role->permissions);
        $this->assertTrue($role->hasPermissionTo('edit-post'));
        $this->assertTrue($role->hasPermissionTo('delete-post'));
    }

    public function test_does_not_sync_when_request_lacks_permissions_key(): void
    {
        $permission = Permission::create(['name' => 'edit-post', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        $this->app->instance('request', $this->jsonRequest([
            'name' => 'updated-editor',
        ]));

        (new RoleObserver())->luminixSaved($role);

        $role->load('permissions');
        $this->assertCount(1, $role->permissions);
        $this->assertTrue($role->hasPermissionTo('edit-post'));
    }

    public function test_clears_permissions_when_empty_array_provided(): void
    {
        $permission = Permission::create(['name' => 'edit-post', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        $this->app->instance('request', $this->jsonRequest([
            'permissions' => [],
        ]));

        (new RoleObserver())->luminixSaved($role);

        $role->load('permissions');
        $this->assertCount(0, $role->permissions);
    }

    public function test_replaces_existing_permissions_on_sync(): void
    {
        Permission::create(['name' => 'edit-post', 'guard_name' => 'web']);
        Permission::create(['name' => 'delete-post', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $role->givePermissionTo('edit-post');

        $this->app->instance('request', $this->jsonRequest([
            'permissions' => ['delete-post'],
        ]));

        (new RoleObserver())->luminixSaved($role);

        $role->load('permissions');
        $this->assertCount(1, $role->permissions);
        $this->assertFalse($role->hasPermissionTo('edit-post'));
        $this->assertTrue($role->hasPermissionTo('delete-post'));
    }
}
