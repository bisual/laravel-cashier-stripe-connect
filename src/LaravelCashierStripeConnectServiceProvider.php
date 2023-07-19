<?php

namespace Bisual\LaravelCashierStripeConnect;

use Bisual\LaravelCashierStripeConnect\Commands\LaravelCashierStripeConnectCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelCashierStripeConnectServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-cashier-stripe-connect');
        // ->hasConfigFile()
        // ->hasViews()
        // ->hasMigration('create_laravel-cashier-stripe-connect_table')
        // ->hasCommand(LaravelCashierStripeConnectCommand::class);
    }
}
