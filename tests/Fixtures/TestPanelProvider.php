<?php

namespace Backstage\Mails\Tests\Fixtures;

use Backstage\Mails\MailsPlugin;
use Filament\Panel;
use Filament\PanelProvider;

class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->topNavigation()
            ->plugin(MailsPlugin::make());
    }
}
