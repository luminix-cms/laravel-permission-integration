<?php

namespace Luminix\LaravelPermissionIntegration\Observers;

use Luminix\LaravelPermissionIntegration\Models\Role;
class RoleObserver
{
    public function luminixSaved(Role $role)
    {
        if (request()->has('permissions')) {
            $role->syncPermissions(...request()->json('permissions'));
        }
    }
}
