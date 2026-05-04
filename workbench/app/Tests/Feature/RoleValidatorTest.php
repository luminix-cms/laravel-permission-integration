<?php

namespace Workbench\App\Tests\Feature;

use Illuminate\Support\Facades\Validator;
use Luminix\LaravelPermissionIntegration\Models\Permission;
use Luminix\LaravelPermissionIntegration\Models\Role;
use Luminix\LaravelPermissionIntegration\Validators\RoleValidator;
use Workbench\App\Tests\FeatureTestCase;

class RoleValidatorTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['auth.guards' => [
            'web' => ['driver' => 'session', 'provider' => 'users'],
            'api' => ['driver' => 'token', 'provider' => 'users'],
        ]]);
    }

    private function storeRules(): array
    {
        return (new RoleValidator())->getValidationRules('store', new Role());
    }

    private function updateRules(Role $role): array
    {
        return (new RoleValidator())->getValidationRules('update', $role);
    }

    public function test_store_requires_name(): void
    {
        $v = Validator::make(['guard_name' => 'web'], $this->storeRules());

        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('name', $v->errors()->toArray());
    }

    public function test_store_requires_guard_name(): void
    {
        $v = Validator::make(['name' => 'editor'], $this->storeRules());

        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('guard_name', $v->errors()->toArray());
    }

    public function test_store_rejects_invalid_guard(): void
    {
        $v = Validator::make(
            ['name' => 'editor', 'guard_name' => 'nonexistent_guard'],
            $this->storeRules()
        );

        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('guard_name', $v->errors()->toArray());
    }

    public function test_store_passes_with_valid_data(): void
    {
        $v = Validator::make(
            ['name' => 'editor', 'guard_name' => 'web'],
            $this->storeRules()
        );

        $this->assertFalse($v->fails());
    }

    public function test_store_permissions_field_is_optional(): void
    {
        $v = Validator::make(
            ['name' => 'editor', 'guard_name' => 'web'],
            $this->storeRules()
        );

        $this->assertFalse($v->fails());
    }

    public function test_store_rejects_nonexistent_permission(): void
    {
        $v = Validator::make([
            'name' => 'editor',
            'guard_name' => 'web',
            'permissions' => ['nonexistent-permission'],
        ], $this->storeRules());

        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('permissions.0', $v->errors()->toArray());
    }

    public function test_store_accepts_existing_permissions(): void
    {
        Permission::create(['name' => 'edit-post', 'guard_name' => 'web']);

        $v = Validator::make([
            'name' => 'editor',
            'guard_name' => 'web',
            'permissions' => ['edit-post'],
        ], $this->storeRules());

        $this->assertFalse($v->fails());
    }

    public function test_store_rejects_non_array_permissions(): void
    {
        $v = Validator::make([
            'name' => 'editor',
            'guard_name' => 'web',
            'permissions' => 'edit-post',
        ], $this->storeRules());

        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('permissions', $v->errors()->toArray());
    }

    public function test_update_name_is_optional(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);

        $v = Validator::make(['guard_name' => 'web'], $this->updateRules($role));

        $this->assertFalse($v->fails());
    }

    public function test_update_guard_name_is_optional(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);

        $v = Validator::make(['name' => 'new-name'], $this->updateRules($role));

        $this->assertFalse($v->fails());
    }

    public function test_update_empty_payload_passes(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);

        $v = Validator::make([], $this->updateRules($role));

        $this->assertFalse($v->fails());
    }

    public function test_update_still_validates_guard_when_provided(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);

        $v = Validator::make(
            ['guard_name' => 'nonexistent_guard'],
            $this->updateRules($role)
        );

        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('guard_name', $v->errors()->toArray());
    }

    public function test_update_rejects_nonexistent_permission(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);

        $v = Validator::make(
            ['permissions' => ['nonexistent-perm']],
            $this->updateRules($role)
        );

        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('permissions.0', $v->errors()->toArray());
    }
}
