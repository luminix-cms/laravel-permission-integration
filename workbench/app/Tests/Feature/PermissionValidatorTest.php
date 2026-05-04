<?php

namespace Workbench\App\Tests\Feature;

use Illuminate\Support\Facades\Validator;
use Luminix\LaravelPermissionIntegration\Models\Permission;
use Luminix\LaravelPermissionIntegration\Validators\PermissionValidator;
use Workbench\App\Tests\FeatureTestCase;

class PermissionValidatorTest extends FeatureTestCase
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
        return (new PermissionValidator())->getValidationRules('store', new Permission());
    }

    private function updateRules(Permission $permission): array
    {
        return (new PermissionValidator())->getValidationRules('update', $permission);
    }

    public function test_store_requires_name(): void
    {
        $v = Validator::make(['guard_name' => 'web'], $this->storeRules());

        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('name', $v->errors()->toArray());
    }

    public function test_store_requires_guard_name(): void
    {
        $v = Validator::make(['name' => 'edit-post'], $this->storeRules());

        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('guard_name', $v->errors()->toArray());
    }

    public function test_store_rejects_invalid_guard(): void
    {
        $v = Validator::make(
            ['name' => 'edit-post', 'guard_name' => 'nonexistent_guard'],
            $this->storeRules()
        );

        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('guard_name', $v->errors()->toArray());
    }

    public function test_store_passes_with_valid_data(): void
    {
        $v = Validator::make(
            ['name' => 'edit-post', 'guard_name' => 'web'],
            $this->storeRules()
        );

        $this->assertFalse($v->fails());
    }

    public function test_store_accepts_api_guard(): void
    {
        $v = Validator::make(
            ['name' => 'edit-post', 'guard_name' => 'api'],
            $this->storeRules()
        );

        $this->assertFalse($v->fails());
    }

    public function test_store_rejects_name_exceeding_max_length(): void
    {
        $v = Validator::make(
            ['name' => str_repeat('a', 256), 'guard_name' => 'web'],
            $this->storeRules()
        );

        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('name', $v->errors()->toArray());
    }

    public function test_update_name_is_optional(): void
    {
        $permission = Permission::create(['name' => 'edit-post', 'guard_name' => 'web']);

        $v = Validator::make(['guard_name' => 'web'], $this->updateRules($permission));

        $this->assertFalse($v->fails());
    }

    public function test_update_guard_name_is_optional(): void
    {
        $permission = Permission::create(['name' => 'edit-post', 'guard_name' => 'web']);

        $v = Validator::make(['name' => 'new-name'], $this->updateRules($permission));

        $this->assertFalse($v->fails());
    }

    public function test_update_empty_payload_passes(): void
    {
        $permission = Permission::create(['name' => 'edit-post', 'guard_name' => 'web']);

        $v = Validator::make([], $this->updateRules($permission));

        $this->assertFalse($v->fails());
    }

    public function test_update_still_validates_guard_when_provided(): void
    {
        $permission = Permission::create(['name' => 'edit-post', 'guard_name' => 'web']);

        $v = Validator::make(
            ['guard_name' => 'nonexistent_guard'],
            $this->updateRules($permission)
        );

        $this->assertTrue($v->fails());
        $this->assertArrayHasKey('guard_name', $v->errors()->toArray());
    }
}
