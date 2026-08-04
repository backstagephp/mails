<?php

namespace Backstage\Mails\Tests\Fixtures;

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
            ->authGuard('web')
            // Filament's scaffolded panel provider sets this; it is not a framework default.
            ->authMiddleware([Authenticate::class])
            ->tenant(Team::class)
            ->plugin(MailsPlugin::make());
    }
}
