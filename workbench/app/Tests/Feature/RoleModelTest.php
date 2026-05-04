<?php

namespace Workbench\App\Tests\Feature;

use Illuminate\Http\Request;
use Luminix\Backend\Model\LuminixModel;
use Luminix\LaravelPermissionIntegration\Models\Permission;
use Luminix\LaravelPermissionIntegration\Models\Role;
use Spatie\Permission\Models\Role as SpatieRole;
use Workbench\App\Tests\FeatureTestCase;

class RoleModelTest extends FeatureTestCase
{
    public function test_extends_spatie_role(): void
    {
        $this->assertInstanceOf(SpatieRole::class, new Role());
    }

    public function test_uses_luminix_model_trait(): void
    {
        $this->assertArrayHasKey(LuminixModel::class, class_uses_recursive(Role::class));
    }

    public function test_fillable_fields(): void
    {
        $this->assertSame(['name', 'guard_name'], (new Role())->getFillable());
    }

    public function test_can_be_created_and_persisted(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);

        $this->assertNotNull($role->id);
        $this->assertDatabaseHas('roles', ['name' => 'editor', 'guard_name' => 'web']);
    }

    public function test_scope_before_luminix_eager_loads_permissions(): void
    {
        $builder = Role::query();
        (new Role())->scopeBeforeLuminix($builder, new Request());

        $this->assertArrayHasKey('permissions', $builder->getEagerLoads());
    }

    public function test_permissions_relationship_works(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $permission = Permission::create(['name' => 'edit-post', 'guard_name' => 'web']);

        $role->givePermissionTo($permission);

        $this->assertTrue($role->hasPermissionTo('edit-post'));
    }

    public function test_get_alias_returns_snake_case_class_name(): void
    {
        $this->assertSame('role', Role::getAlias());
    }
}
