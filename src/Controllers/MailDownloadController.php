<?php

namespace Backstage\Mails\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MailDownloadController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $mailModel = Config::string('mails.models.mail');

        $mail = $mailModel::findOrFail($request->route('mail'));

        // canManageMails() answers "may this user use the mail log at all"; a host with a policy
        // for the mail model also gets to answer "may they see this particular mail".
        if (Gate::getPolicyFor($mail) !== null) {
            Gate::authorize('view', $mail);
        }

        $attachment = $mail->attachments()->findOrFail($request->route('attachment'));

        return $attachment->downloadFileFromStorage();
    }
}
