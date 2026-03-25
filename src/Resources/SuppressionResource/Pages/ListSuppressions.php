<?php

namespace Backstage\Mails\Resources\SuppressionResource\Pages;

use Backstage\Mails\MailsPlugin;
use Backstage\Mails\Resources\SuppressionResource;
use Filament\Resources\Pages\ListRecords;

class ListSuppressions extends ListRecords
{
    protected static string $resource = SuppressionResource::class;

    public function getTitle(): string
    {
        return __('Suppressions');
    }

    public static function canAccess(array $parameters = []): bool
    {
        return MailsPlugin::get()->userCanManageMails();
    }
}
