# Extension library for Laravel Cashier that adds Stripe Connect functionality

[![Latest Version on Packagist](https://img.shields.io/packagist/v/bisual/laravel-cashier-stripe-connect.svg?style=flat-square)](https://packagist.org/packages/bisual/laravel-cashier-stripe-connect)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/bisual/laravel-cashier-stripe-connect/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/bisual/laravel-cashier-stripe-connect/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/bisual/laravel-cashier-stripe-connect/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/bisual/laravel-cashier-stripe-connect/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/bisual/laravel-cashier-stripe-connect.svg?style=flat-square)](https://packagist.org/packages/bisual/laravel-cashier-stripe-connect)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require bisual/laravel-cashier-stripe-connect
```

## Usage

In order to add the Stripe Connectable functionality, you must add the StripeConnectable Trait to your model.

```php
use Bisual\LaravelCashierStripeConnect\Traits\StripeConnectable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable {
    use StripeConnectable;
}
```

After that, make sure that your model has an stripe_connect_id and stripe_status field on database. If you want to customize the key of the attributes, you must do the following:

```php
use Bisual\LaravelCashierStripeConnect\Traits\StripeConnectable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable {
    use StripeConnectable;

    protected $stripe_connect_db_key = "stripe_connect_id";
    protected $stripe_status_db_key = "stripe_status";
}
```

After that, you must implement the abstract functions.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Pol Ribas](https://github.com/polribas14)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
