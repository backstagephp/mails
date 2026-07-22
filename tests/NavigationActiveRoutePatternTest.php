<?php

use Backstage\Mails\Resources\EventResource;
use Backstage\Mails\Resources\MailResource;
use Backstage\Mails\Resources\SuppressionResource;
use Illuminate\Support\Str;

it('does not match sibling resource routes as active', function () {
    $patterns = MailResource::getNavigationItemActiveRoutePattern();

    $patterns = is_array($patterns) ? $patterns : [$patterns];

    $eventRoute = EventResource::getRouteBaseName() . '.index';
    $suppressionRoute = SuppressionResource::getRouteBaseName() . '.index';

    expect(collect($patterns)->contains(fn (string $pattern) => Str::is($pattern, $eventRoute)))
        ->toBeFalse('MailResource active pattern should not match EventResource routes');

    expect(collect($patterns)->contains(fn (string $pattern) => Str::is($pattern, $suppressionRoute)))
        ->toBeFalse('MailResource active pattern should not match SuppressionResource routes');
});

it('matches its own page routes as active', function () {
    $patterns = MailResource::getNavigationItemActiveRoutePattern();

    $patterns = is_array($patterns) ? $patterns : [$patterns];

    $baseName = MailResource::getRouteBaseName();

    foreach (array_keys(MailResource::getPages()) as $page) {
        expect(collect($patterns)->contains(fn (string $pattern) => Str::is($pattern, "{$baseName}.{$page}")))
            ->toBeTrue("MailResource active pattern should match its own '{$page}' route");
    }
});
