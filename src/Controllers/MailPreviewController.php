<?php

namespace Backstage\Mails\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class MailPreviewController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $mailModel = Config::string('mails.models.mail');

        $mail = $mailModel::findOrFail($request->route('mail'));

        return response($mail->html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Security-Policy' => "sandbox; frame-ancestors 'self'",
            'Referrer-Policy' => 'no-referrer',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }
}
