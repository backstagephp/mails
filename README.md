# View all sent emails and events from your Laravel app using Filament

Backstage Mails

[![Latest Version on Packagist](https://img.shields.io/packagist/v/backstage/mails.svg?style=flat-square)](https://packagist.org/packages/backstage/mails)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/backstagephp/mails/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/backstagephp/mails/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/backstagephp/mails/fix-php-code-styling.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/backstagephp/mails/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/backstage/mails.svg?style=flat-square)](https://packagist.org/packages/backstage/mails)

## Nice to meet you, we're [Backstage](https://backstagephp.com)

Hi! We are a web development agency from Nijmegen in the Netherlands and we use Laravel for everything: advanced websites with a lot of bells and whistles and large web applications.

## About the package

Mails can collect everything you might want to track about the mails that has been sent by your Filament app. Common use cases are provided in this package:

-   Log all sent emails with only specific attributes
-   View all sent emails in the browser using the viewer
-   Collect feedback about the delivery from email providers using webhooks
-   Get automatically notified when email bounces
-   Prune logging of emails periodically
-   Resend logged email to same or another recipient

## Upcoming features

-   We're currently in the process of writing mail events support for other popular email service providers like Resend, SendGrid, Amazon SES and Mailtrap.
-   Relate emails being send in Laravel directly to Eloquent models, for example the order confirmation email attached to an Order model.

## Why this package

Email as a protocol is very error prone. Succesfull email delivery is not guaranteed in any way, so it is best to monitor your email sending realtime. Using external services like Postmark, Mailgun or Resend email gets better by offering things like logging and delivery feedback, but it still needs your attention and can fail silently but horendously. Therefore we created Laravel Mails that fills in all the gaps.

The package is built on top of [Laravel Mails](https://github.com/backstagephp/laravel-mails).

![Mails](https://raw.githubusercontent.com/backstagephp/mails/main/docs/mails.jpeg)

## Installation

You can install the package via composer:

```bash
composer require backstage/mails
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="mails-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="mails-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="mails-views"
```

Add the routes to the PanelProvider using the `routes()` method, like this:

```php
use Backstage\Mails\Facades\Mails;

public function panel(Panel $panel): Panel
{
    return $panel
        ->routes(fn () => Mails::routes());
}
```

Then add the plugin to your `PanelProvider`

```php
use Backstage\Mails\MailsPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(MailsPlugin::make());
}
```

### Security

Using the `canManageMails()` method, you can define which users have access to the mail resources/pages. Here's a comprehensive example that includes additional logic for flexibility:

```php
use Backstage\Mails\MailsPlugin;
use Illuminate\Support\Facades\Auth;

$panel
    ->plugins([
        MailsPlugin::make()
            ->canManageMails(function () {
                $user = Auth::user();

                // Allow access for users with specific roles
                if ($user->hasRole('admin') || $user->hasRole('supervisor')) {
                    return true;
                }

                // Allow access for users with specific permissions
                if ($user->hasPermissionTo('manage mails')) {
                    return true;
                }

                // Restrict access for all other users
                return false;
            }),
    ]);
```

This example demonstrates how to combine role-based and permission-based access control, providing a more robust and flexible approach to managing access to mail resources.

### Tenant middleware and route protection

If you want to protect the mail routes with your (tenant) middleware, you can do so by adding the routes to the `tenantRoutes`:

```php
use Backstage\Mails\MailsPlugin;
use Backstage\Mails\Facades\Mails;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugin(MailsPlugin::make())
        ->tenantRoutes(fn() => Mails::routes());
}
```

> [!IMPORTANT]
> For setting up the webhooks to register mail events, please look into the README of [Laravel Mails](https://github.com/backstagephp/laravel-mails), the underlying package that powers this package.

### Configuration

Sometimes you want to customize the resource, like configuring which users or roles may access the resource. You can do this by overriding the `MailResource` or `EventResource` classes in the `mails` config file. Make sure your custom resource extends the original resource.

```php
return [
    'resources' => [
        'mail' => \App\Filament\Resources\MailResource::class,
        'event' => \App\Filament\Resources\EventResource::class,
        'suppression' => \App\Filament\Resources\SuppressionResource::class
    ],
];
```

## Features and screenshots

### List with all sent emails and statistics

The package provides a clear overview of all emails, including statistics and the ability to filter the data.
![Mails](https://raw.githubusercontent.com/backstagephp/mails/main/docs/mails-list.png)

### Resending emails

You can resend emails to the same or another recipient(s). This is useful when your email has bounced and you want to resend it.
![Mails](https://raw.githubusercontent.com/backstagephp/mails/main/docs/mail-resend.png)

### Information

You can view all relevant information about the email, such as the subject, the body, the attachments, the from address, the to address(es), the cc address(es), the bcc address(es), the reply to address, metadata and much more.
![Mails](https://raw.githubusercontent.com/backstagephp/mails/main/docs/mail-sender-information.png)
![Mails](https://raw.githubusercontent.com/backstagephp/mails/main/docs/mail-statistics.png)
![Mails](https://raw.githubusercontent.com/backstagephp/mails/main/docs/mail-events.png)
![Mails](https://raw.githubusercontent.com/backstagephp/mails/main/docs/mail-attachments.png)

### Preview email

The package provides a preview of the email. This is useful to quickly check if the email is correct.
![Mails](https://raw.githubusercontent.com/backstagephp/mails/main/docs/mail-preview.png)

We also provide the raw HTML and plain text of the email.
![Mails](https://raw.githubusercontent.com/backstagephp/mails/main/docs/mail-raw-html.png)

### Events

The package also logs all events that are fired when an email is sent. This is useful to track the email sending process.
![Mails](https://raw.githubusercontent.com/backstagephp/mails/main/docs/events-list.png)
![Mails](https://raw.githubusercontent.com/backstagephp/mails/main/docs/event-details.png)

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

-   [Baspa](https://github.com/backstagephp)
-   [Mark van Eijk](https://github.com/markvaneijk)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
