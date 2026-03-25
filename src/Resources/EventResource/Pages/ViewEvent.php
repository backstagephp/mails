<?php

namespace Backstage\Mails\Resources\EventResource\Pages;

use Backstage\Mails\MailsPlugin;
use Backstage\Mails\Resources\EventResource;
use Filament\Resources\Pages\ViewRecord;

class ViewEvent extends ViewRecord
{
    public static function canAccess(array $parameters = []): bool
    {
        return MailsPlugin::get()->userCanManageMails();
    }

    public static function getResource(): string
    {
        return config('mails.resources.event', EventResource::class);
    }
}
