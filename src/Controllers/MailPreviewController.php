<?php

namespace Backstage\Mails\Controllers;

use Backstage\Mails\Laravel\Models\Mail;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MailPreviewController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var Mail $mail */
        $mail = Mail::find($request->mail);

        return response($mail->html);
    }
}
