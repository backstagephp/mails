<?php

use Backstage\Mails\Resources\EventResource;
use Backstage\Mails\Resources\MailResource;
use Backstage\Mails\Resources\SuppressionResource;

return [
    'resources' => [
        'mail' => MailResource::class,
        'event' => EventResource::class,
        'suppression' => SuppressionResource::class,
    ],

    'navigation' => [
        'group' => null,
        'sort' => null,
    ],
];
