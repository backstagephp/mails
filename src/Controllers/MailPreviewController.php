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

        $resizeScript = <<<HTML
        <script>
            function postHeight() {
                var height = document.documentElement.scrollHeight || document.body.scrollHeight;
                window.parent.postMessage({ type: 'mails-iframe-resize', mailId: '{$mail->id}', height: height }, '*');
            }
            window.addEventListener('load', postHeight);
            window.addEventListener('resize', postHeight);
            new MutationObserver(postHeight).observe(document.body, { childList: true, subtree: true });
        </script>
        HTML;

        $html = $mail->html . $resizeScript;

        return response($html);
    }
}
