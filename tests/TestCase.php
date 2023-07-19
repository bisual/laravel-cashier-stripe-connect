<?php

namespace Bisual\LaravelCashierStripeConnect\Tests;

use Bisual\LaravelCashierStripeConnect\LaravelCashierStripeConnectServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Bisual\\LaravelCashierStripeConnect\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelCashierStripeConnectServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-cashier-stripe-connect_table.php.stub';
        $migration->up();
        */
    }
}
