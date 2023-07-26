<?php

namespace Bisual\LaravelCashierStripeConnect\Traits;

trait UseStripe
{
    private static function getStripeInstance()
    {
        return new \Stripe\StripeClient(config('cashier.secret'));
    }
}
