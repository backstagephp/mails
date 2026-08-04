<?php

use Backstage\Mails\Controllers\MailDownloadController;
use Backstage\Mails\Controllers\MailPreviewController;
use Illuminate\Support\Facades\Route;

function routesNamed(string $name): array
{
    return collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($route) => $route->getName() === $name)
        ->map(fn ($route) => $route->uri())
        ->values()
        ->all();
}

it('registers the mail preview route on panels using the plugin', function () {
    $route = Route::getRoutes()->getByName('filament.admin.mails.preview');

    expect($route)->not->toBeNull()
        ->and($route->uri())->toBe('admin/mails/{mail}/preview')
        ->and($route->getActionName())->toStartWith(MailPreviewController::class);
});

it('registers the attachment download route on panels using the plugin', function () {
    $route = Route::getRoutes()->getByName('filament.admin.mails.attachment.download');

    expect($route)->not->toBeNull()
        ->and($route->uri())->toBe('admin/mails/{mail}/attachment/{attachment}/{filename}')
        ->and($route->getActionName())->toStartWith(MailDownloadController::class);
});

it('registers the mail routes behind the tenant segment on tenancy panels', function () {
    $preview = Route::getRoutes()->getByName('filament.tenancy.mails.preview');
    $download = Route::getRoutes()->getByName('filament.tenancy.mails.attachment.download');

    expect($preview)->not->toBeNull()
        ->and($preview->uri())->toBe('tenancy/{tenant}/mails/{mail}/preview')
        ->and($download)->not->toBeNull()
        ->and($download->uri())->toBe('tenancy/{tenant}/mails/{mail}/attachment/{attachment}/{filename}');
});

it('does not register a route name more than once per panel', function (string $name) {
    expect(routesNamed($name))->toHaveCount(1);
})->with([
    'filament.admin.mails.preview',
    'filament.admin.mails.attachment.download',
    'filament.tenancy.mails.preview',
    'filament.tenancy.mails.attachment.download',
]);
