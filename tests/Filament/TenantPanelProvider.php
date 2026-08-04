<?php

namespace Backstage\Mails\Tests\Filament;

use Backstage\Mails\MailsPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Panel;
use Filament\PanelProvider;

class TenantPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('tenancy')
            ->path('tenancy')
            ->login()
            ->tenant(Team::class)
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugin(MailsPlugin::make());
    }
}
