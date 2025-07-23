<?php

namespace Egmond\InertiaTables;

use Egmond\InertiaTables\Commands\InertiaTablesCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class InertiaTablesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('inertia-tables')
            ->hasRoutes('web')
            ->hasMigration('create_inertia_tables_table')
            ->hasCommand(InertiaTablesCommand::class);
    }
}
