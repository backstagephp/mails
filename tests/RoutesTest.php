<?php

use Illuminate\Support\Facades\Route;

function mailRoutesNamed(string $name): array
{
    return collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($route) => $route->getName() === $name)
        ->map(fn ($route) => $route->uri())
        ->values()
        ->all();
}

it('registers the mail routes on panels using the plugin', function () {
    expect(Route::getRoutes()->getByName('filament.admin.mails.preview'))->not->toBeNull()
        ->and(Route::getRoutes()->getByName('filament.admin.mails.attachment.download'))->not->toBeNull();
});

it('registers the mail routes behind the tenant segment on tenancy panels', function () {
    $route = Route::getRoutes()->getByName('filament.tenancy.mails.preview');

    expect($route)->not->toBeNull()
        ->and($route->uri())->toBe('tenancy/{tenant}/mails/{mail}/preview');
});

it('does not register a route name more than once per panel', function (string $name) {
    expect(mailRoutesNamed($name))->toHaveCount(1);
})->with([
    'filament.admin.mails.preview',
    'filament.admin.mails.attachment.download',
    'filament.tenancy.mails.preview',
    'filament.tenancy.mails.attachment.download',
]);
