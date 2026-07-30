<?php

namespace Backstage\Mails\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;

class MailDownloadController extends Controller
{
    public function __invoke(Request $request)
    {
        $mailModel = Config::get('mails.models.mail');

        $mail = $mailModel::findOrFail($request->route('mail'));

        $attachment = $mail->attachments()->findOrFail($request->route('attachment'));

        return $attachment->downloadFileFromStorage();
    }
}
