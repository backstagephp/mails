<?php

namespace Backstage\Mails;

use Backstage\Mails\Controllers\MailDownloadController;
use Backstage\Mails\Controllers\MailPreviewController;
use Backstage\Mails\Http\Middleware\EnsureUserCanManageMails;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

class Mails
{
    public static function routes(): void
    {
        Route::middleware([
            Authenticate::class,
            EnsureUserCanManageMails::class,
        ])->group(function (): void {
            Route::get('mails/{mail}/preview', MailPreviewController::class)
                ->name('mails.preview');

            Route::get('mails/{mail}/attachment/{attachment}/{filename}', MailDownloadController::class)
                ->name('mails.attachment.download');
        });
    }
}
