<?php

namespace Bisual\LaravelCashierStripeConnect\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Bisual\LaravelCashierStripeConnect\LaravelCashierStripeConnect
 */
class LaravelCashierStripeConnect extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Bisual\LaravelCashierStripeConnect\LaravelCashierStripeConnect::class;
    }
}
