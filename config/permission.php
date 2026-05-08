<?php

/**
 * 
 * This is the configuration file for the luminix/laravel-permission-integration package.
 * Location: config/luminix/permission.php
 * 
 */
return [

    /**
     * The permission to set roles when creating or editing users.
     * If set to null, all users capable of creating or editing users will be able to set roles.
     */
    'permission_to_set_roles' => 'set-roles',

];
