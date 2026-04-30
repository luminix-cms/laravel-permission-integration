<?php

namespace Luminix\LaravelPermissionIntegration\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Http\Request;
use Luminix\Backend\Model\LuminixModel;
use Luminix\Backend\Validation\WithValidator;
use Luminix\LaravelPermissionIntegration\Observers\RoleObserver;
use Luminix\LaravelPermissionIntegration\Validators\RoleValidator;
use Spatie\Permission\Models\Role as BaseRole;

#[WithValidator(RoleValidator::class)]
#[ObservedBy(RoleObserver::class)]
class Role extends BaseRole
{
    
    use LuminixModel;

    protected $fillable = [
        'name',
        'guard_name'
    ];

    public function scopeBeforeLuminix(Builder $query, Request $request)
    {
        $query->with('permissions');
    }

}
