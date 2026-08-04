<?php

use Backstage\Mails\Resources\EventResource\Pages\ListEvents;
use Backstage\Mails\Resources\EventResource\Pages\ViewEvent;
use Backstage\Mails\Resources\MailResource\Pages\ListMails;
use Backstage\Mails\Resources\MailResource\Pages\ViewMail;
use Backstage\Mails\Resources\SuppressionResource\Pages\ListSuppressions;

it('loads every page against the installed Filament', function (string $page) {
    // Loading a page whose override no longer matches Filament's signature is a
    // fatal error, so simply resolving the class is the assertion here.
    expect(class_exists($page))->toBeTrue();
})->with([
    ListMails::class,
    ViewMail::class,
    ListEvents::class,
    ViewEvent::class,
    ListSuppressions::class,
]);

it('accepts the parent action argument in every shape Filament passes', function () {
    $parameter = (new ReflectionMethod(ListMails::class, 'unmountAction'))->getParameters()[0];

    // Filament <5.7 passes a bool, 5.7+ passes null or the name of the parent
    // action to cancel to, so the override has to accept all three.
    expect((string) $parameter->getType())->toContain('bool')
        ->and((string) $parameter->getType())->toContain('string')
        ->and($parameter->allowsNull())->toBeTrue();
});
