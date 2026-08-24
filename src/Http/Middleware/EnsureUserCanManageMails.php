<?php

namespace Backstage\Mails\Http\Middleware;

use Backstage\Mails\MailsPlugin;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanManageMails
{
    public function handle(Request $request, Closure $next): Response
    {
        $panel = Filament::getCurrentPanel();

        abort_if($panel === null || ! $panel->hasPlugin('mails'), 403);

        abort_unless(MailsPlugin::get()->userCanManageMails(), 403);

        return $next($request);
    }
}
