<?php

namespace Backstage\Mails\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;

class MailPreviewController extends Controller
{
    public function __invoke(Request $request)
    {
        $mailModel = Config::get('mails.models.mail');

        $mail = $mailModel::findOrFail($request->route('mail'));

        return response($mail->html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "frame-ancestors 'self'",
        ]);
    }
}
