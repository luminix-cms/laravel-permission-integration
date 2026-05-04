<?php

namespace Workbench\App\Tests;

use Orchestra\Testbench\Concerns\WithWorkbench;
use Workbench\Database\Seeders\DatabaseSeeder;

use function Orchestra\Testbench\artisan;


class TestCase extends \Orchestra\Testbench\TestCase
{

    use WithWorkbench;

    protected function getPackageProviders($app)
    {
        return [
            \Luminix\LaravelPermissionIntegration\PermissionServiceProvider::class,
            \Workbench\App\Providers\WorkbenchServiceProvider::class,
        ];
    }


    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations() 
    {
        artisan($this, 'migrate', ['--database' => 'testing']);

        $this->beforeApplicationDestroyed(
            fn () => artisan($this, 'migrate:rollback', ['--database' => 'testing'])
        );
    }
}