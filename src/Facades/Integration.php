<?php

namespace Luminix\LaravelPermissionIntegration\Facades;

use Illuminate\Support\Facades\Facade;
use Luminix\LaravelPermissionIntegration\Services\IntegrationService;

/**
 * 
 * @method static array getAvailableGuards()
 * @method static void makeLuminixFindModels()
 * @method static void addFrontendConfigurations()
 * @method static void setRoleableModelsApis()
 * 
 */
class Integration extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return IntegrationService::class;
    }

}
