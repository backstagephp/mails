<?php

namespace Backstage\Mails\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class MailPreviewController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $mailModel = Config::string('mails.models.mail');

        $mail = $mailModel::findOrFail($request->route('mail'));

        // canManageMails() answers "may this user use the mail log at all"; a host with a policy
        // for the mail model also gets to answer "may they see this particular mail".
        if (Gate::getPolicyFor($mail) !== null) {
            Gate::authorize('view', $mail);
        }

        return response($mail->html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Security-Policy' => "sandbox; frame-ancestors 'self'",
            'Referrer-Policy' => 'no-referrer',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }
}
