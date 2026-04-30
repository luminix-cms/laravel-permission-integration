<?php

namespace Luminix\LaravelPermissionIntegration\Services;

class IntegrationService
{
    public function getAvailableGuards()
    {
        return array_keys(config('auth.guards'));
    }
}
