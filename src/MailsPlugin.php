<?php

namespace Backstage\Mails;

use Backstage\Mails\Resources\EventResource;
use Backstage\Mails\Resources\MailResource;
use Backstage\Mails\Resources\SuppressionResource;
use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Filament\Support\Concerns\EvaluatesClosures;

class MailsPlugin implements Plugin
{
    use EvaluatesClosures;

    public bool | Closure $canManageMails = true;

    public function getId(): string
    {
        return 'mails';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->colors([
                'clicked' => Color::Purple,
            ])
            ->resources([
                config('mails.resources.mail', MailResource::class),
                config('mails.resources.event', EventResource::class),
                config('mails.resources.suppression', SuppressionResource::class),
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function canManageMails(bool | Closure $canManageMails = true): static
    {
        $this->canManageMails = $canManageMails;

        return $this;
    }

    public function userCanManageMails(): bool
    {
        return $this->evaluate($this->canManageMails);
    }
}
