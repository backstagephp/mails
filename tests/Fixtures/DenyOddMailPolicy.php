<?php

namespace Backstage\Mails\Tests\Fixtures;

use Backstage\Mails\Laravel\Models\Mail;

class DenyOddMailPolicy
{
    public function view(User $user, Mail $mail): bool
    {
        return $mail->getKey() % 2 === 0;
    }
}
