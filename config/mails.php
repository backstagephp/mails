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

    'cache' => [
        // Seconds to cache the status counts shown in the index tab badges and
        // the stats widget. These run a count query per status on every page
        // load, which does not scale on large tables. Set to 0/null to disable.
        'counts_ttl' => 60,
    ],
];
