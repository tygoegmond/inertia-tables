<?php

namespace Egmond\InertiaTables\Tests;

use Egmond\InertiaTables\InertiaTablesServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Egmond\\InertiaTables\\Tests\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            InertiaTablesServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set application key for encryption and signed URLs
        config()->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        config()->set('app.cipher', 'AES-256-CBC');
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }
}
