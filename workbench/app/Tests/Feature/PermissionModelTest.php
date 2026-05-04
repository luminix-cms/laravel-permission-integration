<?php

namespace Workbench\App\Tests\Feature;

use Luminix\Backend\Model\LuminixModel;
use Luminix\LaravelPermissionIntegration\Models\Permission;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Workbench\App\Tests\FeatureTestCase;

class PermissionModelTest extends FeatureTestCase
{
    public function test_extends_spatie_permission(): void
    {
        $this->assertInstanceOf(SpatiePermission::class, new Permission());
    }

    public function test_uses_luminix_model_trait(): void
    {
        $this->assertArrayHasKey(LuminixModel::class, class_uses_recursive(Permission::class));
    }

    public function test_fillable_fields(): void
    {
        $this->assertSame(['name', 'guard_name'], (new Permission())->getFillable());
    }

    public function test_can_be_created_and_persisted(): void
    {
        $permission = Permission::create(['name' => 'edit-post', 'guard_name' => 'web']);

        $this->assertNotNull($permission->id);
        $this->assertDatabaseHas('permissions', ['name' => 'edit-post', 'guard_name' => 'web']);
    }

    public function test_get_alias_returns_snake_case_class_name(): void
    {
        $this->assertSame('permission', Permission::getAlias());
    }

    public function test_duplicate_name_and_guard_throws(): void
    {
        Permission::create(['name' => 'edit-post', 'guard_name' => 'web']);

        $this->expectException(\Spatie\Permission\Exceptions\PermissionAlreadyExists::class);
        Permission::create(['name' => 'edit-post', 'guard_name' => 'web']);
    }
}
