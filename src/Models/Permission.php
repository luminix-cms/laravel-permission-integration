<?php

namespace Luminix\LaravelPermissionIntegration\Models;

use Spatie\Permission\Models\Permission as BasePermission;
use Luminix\Backend\Model\LuminixModel;
use Luminix\Backend\Validation\WithValidator;
use Luminix\LaravelPermissionIntegration\Validators\PermissionValidator;

#[WithValidator(PermissionValidator::class)]
class Permission extends BasePermission
{

    use LuminixModel;

    protected $fillable = [
        'name',
        'guard_name'
    ];

}
