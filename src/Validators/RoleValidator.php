<?php

namespace Luminix\LaravelPermissionIntegration\Validators;

use Illuminate\Validation\Rule;
use Luminix\Backend\Validation\Validator;
use Luminix\LaravelPermissionIntegration\Facades\Integration;

class RoleValidator extends Validator
{
    public function store(): array
    {
        return [
            'name' => 'required|string|max:255',
            'guard_name' => [
                'required',
                'string',
                Rule::in(Integration::getAvailableGuards())
            ],
            'permissions' => 'sometimes|array',
            'permissions.*.name' => 'string|exists:permissions,name'
        ];
    }

    public function update($item): array
    {
        return $this->store();
    }
}
