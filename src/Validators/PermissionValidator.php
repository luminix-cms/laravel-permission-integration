<?php

namespace Luminix\LaravelPermissionIntegration\Validators;

use Illuminate\Validation\Rule;
use Luminix\Backend\Validation\Validator;
use Luminix\LaravelPermissionIntegration\Facades\Integration;

class PermissionValidator extends Validator
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
        ];
    }

    public function update($item): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'guard_name' => [
                'sometimes',
                'string',
                Rule::in(Integration::getAvailableGuards())
            ],
        ];
    }
}
