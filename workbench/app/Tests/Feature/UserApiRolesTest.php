<?php

namespace Workbench\App\Tests\Feature;

use Illuminate\Foundation\Testing\Concerns\InteractsWithAuthentication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Luminix\LaravelPermissionIntegration\Facades\Integration;
use Luminix\LaravelPermissionIntegration\Models\Permission;
use Luminix\LaravelPermissionIntegration\Models\Role;
use Workbench\App\Models\User;
use Workbench\App\Tests\FeatureTestCase;

class UserApiRolesTest extends FeatureTestCase
{
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);
        $app->config->set('auth.providers.users.model', User::class);
    }

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function jsonRequest(array $data): Request
    {
        return Request::create(
            '/',
            'PUT',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
    }

    private function createUser(string $name = 'User'): User
    {
        static $counter = 0;
        $counter++;
        return User::create([
            'name'     => $name,
            'email'    => "user{$counter}@example.com",
            'password' => Hash::make('password'),
        ]);
    }

    public function test_syncs_roles_when_no_permission_required(): void
    {
        config(['luminix.permission.permission_to_set_roles' => null]);

        Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $user = $this->createUser();

        $this->app->instance('request', $this->jsonRequest(['roles' => ['editor']]));

        $user->fireLuminixEvent('saving', false);

        $this->assertTrue($user->fresh()->hasRole('editor'));
    }

    public function test_syncs_roles_when_actor_has_set_roles_permission(): void
    {
        Permission::create(['name' => 'set-roles', 'guard_name' => 'web']);
        Role::create(['name' => 'editor', 'guard_name' => 'web']);

        $admin = $this->createUser('Admin');
        $admin->givePermissionTo('set-roles');
        $this->actingAs($admin);

        $user = $this->createUser();
        $this->app->instance('request', $this->jsonRequest(['roles' => ['editor']]));

        $user->fireLuminixEvent('saving', false);

        $this->assertTrue($user->fresh()->hasRole('editor'));
    }

    public function test_does_not_sync_roles_when_actor_lacks_permission(): void
    {
        Role::create(['name' => 'editor', 'guard_name' => 'web']);

        $actor = $this->createUser('Actor');
        $this->actingAs($actor);

        $user = $this->createUser();
        $this->app->instance('request', $this->jsonRequest(['roles' => ['editor']]));

        $user->fireLuminixEvent('saving', false);

        $this->assertFalse($user->fresh()->hasRole('editor'));
    }

    public function test_does_not_sync_roles_when_unauthenticated(): void
    {
        Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $user = $this->createUser();

        $this->app->instance('request', $this->jsonRequest(['roles' => ['editor']]));

        $user->fireLuminixEvent('saving', false);

        $this->assertFalse($user->fresh()->hasRole('editor'));
    }

    public function test_does_not_touch_roles_when_request_lacks_roles_key(): void
    {
        config(['luminix.permission.permission_to_set_roles' => null]);

        Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $user = $this->createUser();
        $user->assignRole('editor');

        $this->app->instance('request', $this->jsonRequest(['name' => 'João']));

        $user->fireLuminixEvent('saving', false);

        $this->assertTrue($user->fresh()->hasRole('editor'));
    }

    public function test_replaces_all_existing_roles_on_sync(): void
    {
        config(['luminix.permission.permission_to_set_roles' => null]);

        Role::create(['name' => 'editor', 'guard_name' => 'web']);
        Role::create(['name' => 'moderador', 'guard_name' => 'web']);
        $user = $this->createUser();
        $user->assignRole('editor');

        $this->app->instance('request', $this->jsonRequest(['roles' => ['moderador']]));

        $user->fireLuminixEvent('saving', false);

        $fresh = $user->fresh();
        $this->assertFalse($fresh->hasRole('editor'));
        $this->assertTrue($fresh->hasRole('moderador'));
    }

    public function test_syncs_multiple_roles_at_once(): void
    {
        config(['luminix.permission.permission_to_set_roles' => null]);

        Role::create(['name' => 'editor', 'guard_name' => 'web']);
        Role::create(['name' => 'moderador', 'guard_name' => 'web']);
        $user = $this->createUser();

        $this->app->instance('request', $this->jsonRequest(['roles' => ['editor', 'moderador']]));

        $user->fireLuminixEvent('saving', false);

        $fresh = $user->fresh();
        $this->assertTrue($fresh->hasRole('editor'));
        $this->assertTrue($fresh->hasRole('moderador'));
    }
}
