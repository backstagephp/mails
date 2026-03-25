<?php

namespace Backstage\Mails;

use Backstage\Mails\Controllers\MailDownloadController;
use Backstage\Mails\Controllers\MailPreviewController;
use Illuminate\Support\Facades\Route;

class Mails
{
    public static function routes(): void
    {
        Route::get('mails/{mail}/preview', MailPreviewController::class)->name('mails.preview');
        Route::get('mails/{mail}/attachment/{attachment}/{filename}', MailDownloadController::class)->name('mails.attachment.download');
    }
}
