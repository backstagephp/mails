<?php

use Backstage\Mails\Controllers\MailDownloadController;
use Backstage\Mails\Controllers\MailPreviewController;
use Illuminate\Support\Facades\Route;

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
