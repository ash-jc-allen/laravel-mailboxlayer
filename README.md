<p align="center">
# Laravel Mailbox Layer
</p>

<p align="center">
<a href="https://packagist.org/packages/ashallendesign/laravel-mailboxlayer"><img src="https://img.shields.io/packagist/v/ashallendesign/laravel-mailboxlayer.svg?style=flat-square" alt="Latest Version on Packagist"></a>
<a href="https://travis-ci.org/ash-jc-allen/laravel-mailboxlayer"><img src="https://img.shields.io/travis/ash-jc-allen/laravel-mailboxlayer/master.svg?style=flat-square" alt="Build Status"></a>
<a href="https://packagist.org/packages/ashallendesign/laravel-mailboxlayer"><img src="https://img.shields.io/packagist/dt/ashallendesign/laravel-mailboxlayer.svg?style=flat-square" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/ashallendesign/laravel-mailboxlayer"><img src="https://img.shields.io/packagist/php-v/ashallendesign/laravel-mailboxlayer?style=flat-square" alt="PHP from Packagist"></a>
<a href="https://github.com/ash-jc-allen/laravel-mailboxlayer/blob/master/LICENSE"><img src="https://img.shields.io/github/license/ash-jc-allen/laravel-mailboxlayer?style=flat-square" alt="GitHub license"></a>
</p>

## Table of Contents

- [Overview](#overview)
- [Installation](#installation)
    - [Requirements](#requirements)
    - [Install the Package](#install-the-package)
    - [Publish the Config and Migrations](#publish-the-config-and-migrations)
    - [Getting Your Mailbox Layer API Key](#getting-your-mailbox-layer-api-key)
- [Usage](#usage)
    - [Methods](#methods)
        - [Validating One Email Address](#validating-one-email-address)
        - [Validating Multiple Email Addresses](#validating-multiple-email-addresses)
    - [Facade](#facade)
    - [Available Validation Result Properties](#available-validation-result-properties)
    - [Caching](#caching)
        - [Caching Validation Results](#caching-validation-results)
        - [Busting the Cached Validation Results](#busting-the-cached-validation-results)
    - [Options](#options)
        - [Using HTTPS](#using-https)
        - [Running an SMTP Check](#running-an-smtp-check)
- [Testing](#testing)
- [Security](#security)
- [Contribution](#contribution)
- [Credits](#credits)
- [Changelog](#changelog)
- [License](#license)
    
## Overview
Laravel Mailbox Layer is a lightweight wrapper Laravel package that can be used for validating email addresses via the
[Mailbox Layer API](https://mailboxlayer.com/). The package supports caching and contains a validation rule so that you can start
validating email addresses instantly.

## Installation

### Requirements
The package has been developed and tested to work with the following minimum requirements:

- PHP 7.3
- Laravel 7

### Install the Package
You can install the package via Composer:

```bash
composer require ashallendesign/laravel-mailboxlayer
```

### Publish the Config and Migrations
You can then publish the package's config file (so that you can make changes to them) by using the following command:
```bash
php artisan vendor:publish --provider="AshAllenDesign\MailboxLayer\Providers\MailboxLayerProvider"
```

### Getting Your Mailbox Layer API Key
To use this package and interact with the Mailbox Layer API, you'll need to register on the [Mailbox Layer API](https://mailboxlayer.com/)
website and get your API key. Once you have the key, you can set it in your ` .env ` file as shown below:

```
MAILBOX_LAYER_API_KEY=your-api-key-here
```

## Usage
### Methods
#### Validating One Email Address

To validate a single email address, you can use the ` check() ` method that is provided in the package. This method returns a ` ValidationResult ` object.

The example below shows how to validate a single email address:

```php
use AshAllenDesign\MailboxLayer\Classes\MailboxLayer;

$mailboxLayer = new MailboxLayer('api-key-here');
$validationResult = $mailboxLayer->check('example@domain.com');
```

#### Validating Multiple Email Addresses

To validate multiple email addresses, you can use the ` checkMany() ` method that is provided in the package. This method returns a ` Collection ` of ` ValidationResult ` objects.

The example below shows how to validate multiple email addresses:

```php
use AshAllenDesign\MailboxLayer\Classes\MailboxLayer;

$mailboxLayer = new MailboxLayer('api-key-here');
$validationResults = $mailboxLayer->checkMany(['example@domain.com', 'test@test.com']);
```


### Facade
If you prefer to use facades in Laravel, you can choose to use the provided ` Mailbox Layer ` facade instead of instantiating the ``` AshAllenDesign\MailboxLayer\Classes\MailboxLayer ```
class manually.

The example below shows an example of how you could use the facade to validate an email address:

```php     
use MailboxLayer;
    
return MailboxLayer::check('example@domain.com');
```

### Available Validation Result Properties


| Field       | Description                                                                                           |
|-------------|-------------------------------------------------------------------------------------------------------|
| email       | The email address that the validation was carried out on.                                             |
| didYouMean  | A suggested email address in case a typo was detected.                                                |
| user        | The local part of the email address. Example: 'mail' in 'mail@ashallendesign.co.uk'.                  |
| domain      | The domain part of the email address. Example: 'ashallendesign.co.uk' in 'mail@ashallendesign.co.uk'. |
| formatValid | Whether or not the syntax of the requested email is valid.                                            |
| mxFound     | Whether or not the MX records for the requested domain could be found.                                |
| smtpCheck   | Whether or not the SMTP check of the requested email address succeeded.                               |
| catchAll    | Whether or not the requested email address is found to be part of a catch-all mailbox.                |
| role        | Whether or not the requested email is a role email address. Example: 'support@ashallendesign.co.uk'.  |
| disposable  | Whether or not the requested email is disposable. Example: 'hello@mailinator.com'.                    |
| free        | Whether or not the requested email is a free email address.                                           |
| score       | A score between 0 and 1 reflecting the quality and deliverability of the requested email address.     |

### Caching
#### Caching Validation Results
There might be times when you want to cache the validation results for an email. This can have significant performance benefits for if
you try to validate the email again, due to the fact that the results will be fetched from the cache rather than from a new API request.

As an example, if you were importing a CSV containing email addresses, you might want to validate each of the addresses. However, if the
CSV contains some duplicated email addresses, it could lead to unnecessary API calls being made. So, by using the caching, each unique
address would only be fetched once from the API. To do this, you can use the ` shouldCache() ` method.

The example below shows how to cache the validation results:

```php
use AshAllenDesign\MailboxLayer\Classes\MailboxLayer;

$mailboxLayer = new MailboxLayer('api-key-here');

// Result fetched from the API.
$validationResults = $mailboxLayer->shouldCache()->check('example@domain.com');

// Result fetched from the cache.
$validationResults = $mailboxLayer->shouldCache()->check('example@domain.com');
```

#### Busting the Cached Validation Results
By default, the package will always try to fetch the validation results from the cache before trying to fetch them via the API.
As mentioned before, this can lead to multiple performance benefits.

However, there may be times that you want to ignore the cached results and make a new request to the API. As an example, you
might have a cached validation result that is over 6 months old and could possibly be outdated or inaccurate, so it's likely
that you want to update the validation data and ensure it is correct. To do this, you can use the ` fresh() ` method.

The example below shows how to fetch a new validation result:

```php
use AshAllenDesign\MailboxLayer\Classes\MailboxLayer;

$mailboxLayer = new MailboxLayer('api-key-here');

$validationResults = $mailboxLayer->fresh()->check('example@domain.com');
```

### Options
#### Using HTTPS

By default, all the API requests are made using HTTPS. However, the [Mailbox Layer API](https://mailboxlayer.com/)
allows for requests to be made using HTTP if needed. This can be particularly useful when working in a local, development environment.
To use HTTP when making the API requests, you can use the ` withHttps() ` method.

Please note, it is not recommended making the requests over HTTP in a live, production environment!

The example below shows how to make the requests using HTTP rather than HTTPS:

```php
use AshAllenDesign\MailboxLayer\Classes\MailboxLayer;

$mailboxLayer = new MailboxLayer('api-key-here');

$validationResults = $mailboxLayer->withHttps(false)->check('example@domain.com');
```

#### Running an SMTP Check

By default, all the API requests will run an SMTP check on the email address. Running this check can improve the accuracy
of the results and give better results. However, according to Mailbox Layer, running this checks take up around 75% of the
API's entire response time.

So, you can reduce the overall runtime before preventing the SMTP check from running by using the ` withSmtpCheck() ` method.

The example below shows how to validate an email address without running an SMTP check:

```php
use AshAllenDesign\MailboxLayer\Classes\MailboxLayer;

$mailboxLayer = new MailboxLayer('api-key-here');

$validationResults = $mailboxLayer->withSmtpCheck(false)->check('example@domain.com');
```


Read more about the SMTP check in the [Mailbox Layer API docs](https://mailboxlayer.com/documentation#smtp_mx_check).

## Testing

```bash
vendor/bin/phpunit
```

## Security

If you find any security related issues, please contact me directly at [mail@ashallendesign.co.uk](mailto:mail@ashallendesign.co.uk) to report it.

## Contribution

If you wish to make any changes or improvements to the package, feel free to make a pull request.

To contribute to this library, please use the following guidelines before submitting your pull request:

- Write tests for any new functions that are added. If you are updating existing code, make sure that the existing tests
pass and write more if needed.
- Follow [PSR-2](https://www.php-fig.org/psr/psr-2/) coding standards.
- Make all pull requests to the ``` master ``` branch.

## Credits

- [Ash Allen](https://ashallendesign.co.uk)
- [All Contributors](https://github.com/ash-jc-allen/laravel-mailboxlayer/graphs/contributors)

## Changelog

Check the [CHANGELOG](CHANGELOG.md) to get more information about the latest changes.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
