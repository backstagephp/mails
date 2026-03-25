<?php

namespace Backstage\Mails\Resources\MailResource\Pages;

use Backstage\Mails\MailsPlugin;
use Backstage\Mails\Resources\MailResource;
use Filament\Resources\Pages\ViewRecord;

class ViewMail extends ViewRecord
{
    public static function canAccess(array $parameters = []): bool
    {
        return MailsPlugin::get()->userCanManageMails();
    }

    public static function getResource(): string
    {
        return config('mails.resources.mail', MailResource::class);
    }
}
