<?php

namespace Luminix\LaravelPermissionIntegration\Observers;

use Luminix\LaravelPermissionIntegration\Models\Role;
use Illuminate\Support\Arr;

class RoleObserver
{
    public function luminixSaved(Role $role)
    {
        if (request()->has('permissions')) {
            $permissions = Arr::pluck(request()->json('permissions'), 'name');
            $role->syncPermissions(...$permissions);
        }
    }
}
