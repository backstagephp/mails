<?php

namespace Backstage\Mails\Tests\Fixtures;

use Backstage\Mails\Laravel\Models\Mail;

class AllowSpecificUserPolicy
{
    public function view(User $user, Mail $mail): bool
    {
        return $user->email === 'allowed@example.com';
    }
}
