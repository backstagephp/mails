<?php

namespace Backstage\Mails\Resources\MailResource\Widgets;

use Backstage\Mails\MailsPlugin;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MailStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected static bool $isDiscovered = false;

    public static function canView(): bool
    {
        return MailsPlugin::get()->userCanManageMails();
    }

    protected function getStats(): array
    {
        $class = config('mails.models.mail');

        $bouncedMails = $class::where(fn ($query) => $query->softBounced()->orWhere(fn ($query) => $query->hardBounced()))->count();
        $openedMails = $class::opened()->count();
        $deliveredMails = $class::delivered()->count();
        $clickedMails = $class::clicked()->count();

        $mailCount = $class::count();

        if ($mailCount === 0) {
            return [];
        }

        $generateUrl = function (string $activeTab): ?string {
            $panel = Filament::getCurrentOrDefaultPanel();
            $tenant = Filament::getTenant();

            if (! $panel || ! $tenant) {
                return null;
            }

            return route('filament.' . $panel->getId() . '.resources.mails.index', [
                'activeTab' => $activeTab,
                'tenant' => $tenant,
            ]);
        };

        $widgets[] = Stat::make(__('Delivered'), number_format(($deliveredMails / $mailCount) * 100, 1) . '%')
            ->label(__('Delivered'))
            ->description($deliveredMails . ' ' . __('of') . ' ' . $mailCount . ' ' . __('emails'))
            ->color('success')
            ->url($generateUrl('delivered'));

        $widgets[] = Stat::make(__('Opened'), number_format(($openedMails / $mailCount) * 100, 1) . '%')
            ->label(__('Opened'))
            ->description($openedMails . ' ' . __('of') . ' ' . $mailCount . ' ' . __('emails'))
            ->color('info')
            ->url($generateUrl('opened'));

        $widgets[] = Stat::make(__('Clicked'), number_format(($clickedMails / $mailCount) * 100, 1) . '%')
            ->label(__('Clicked'))
            ->description($clickedMails . ' ' . __('of') . ' ' . $mailCount . ' ' . __('emails'))
            ->color('clicked')
            ->url($generateUrl('clicked'));

        $widgets[] = Stat::make(__('Bounced'), number_format(($bouncedMails / $mailCount) * 100, 1) . '%')
            ->label(__('Bounced'))
            ->description($bouncedMails . ' ' . __('of') . ' ' . $mailCount . ' ' . __('emails'))
            ->color('danger')
            ->url($generateUrl('bounced'));

        return $widgets;
    }
}
