<?php

namespace Egmond\InertiaTables;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Egmond\InertiaTables\Commands\InertiaTablesCommand;

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
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_inertia_tables_table')
            ->hasCommand(InertiaTablesCommand::class);
    }
}
