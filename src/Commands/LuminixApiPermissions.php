<?php

namespace Luminix\LaravelPermissionIntegration\Commands;

use Illuminate\Console\Command;
use Luminix\Backend\Facades\Finder;
use Luminix\LaravelPermissionIntegration\Facades\Integration;

class LuminixApiPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luminix:api-permissions
                                {--guard=*} Guard to create permissions for (repeatable)';

    /**
     * The console command description.
     * 
     * @var string
     */
    protected $description = 'Creates missing permissions used by the Luminix API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $guards = $this->option('guard') ?? [Integration::getAvailableGuards()[0]];

        $Permission = config('permission.models.permission');

        $crud = [
            'create',
            'read',
            'update',
            'delete',
        ];

        Finder::all()->keys()->each(function ($key) use ($guards, $crud, $Permission) {
            foreach ($crud as $operation) {
                foreach ($guards as $guard) {
                    $Permission::insertOrIgnore([
                        'name' => "{$operation}-{$key}",
                        'guard_name' => $guard,
                    ]);
                }
            }
        });
    }

}
