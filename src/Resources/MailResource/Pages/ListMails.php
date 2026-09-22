<?php

namespace Backstage\Mails\Resources\MailResource\Pages;

use Backstage\Mails\MailsPlugin;
use Backstage\Mails\Resources\MailResource;
use Backstage\Mails\Resources\MailResource\Widgets\MailStatsWidget;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMails extends ListRecords
{
    public static function canAccess(array $parameters = []): bool
    {
        return MailsPlugin::get()->userCanManageMails();
    }

    public static function getResource(): string
    {
        return config('mails.resources.mail', MailResource::class);
    }

    public function getTitle(): string
    {
        return __('Emails');
    }

    protected function getActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        $counts = MailResource::getStatusCounts();

        return [
            'all' => Tab::make()
                ->label(__('All'))
                ->badgeColor('primary')
                ->icon('heroicon-o-inbox-stack')
                ->badge($counts['all']),

            'unsent' => Tab::make()
                ->label(__('Unsent'))
                ->badgeColor('gray')
                ->icon('heroicon-o-inbox')
                ->badge($counts['unsent'])
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->unsent()),

            'sent' => Tab::make()
                ->label(__('Sent'))
                ->badgeColor('info')
                ->icon('heroicon-o-paper-airplane')
                ->badge($counts['sent'])
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->sent()),

            'delivered' => Tab::make()
                ->label(__('Delivered'))
                ->badgeColor('success')
                ->icon('heroicon-o-inbox-arrow-down')
                ->badge($counts['delivered'])
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->delivered()),

            'opened' => Tab::make()
                ->label(__('Opened'))
                ->badgeColor('info')
                ->icon('heroicon-o-envelope-open')
                ->badge($counts['opened'])
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->opened()),

            'clicked' => Tab::make()
                ->label(__('Clicked'))
                ->badgeColor('clicked')
                ->icon('heroicon-o-cursor-arrow-rays')
                ->badge($counts['clicked'])
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->clicked()),

            'bounced' => Tab::make()
                ->label(__('Bounced'))
                ->badgeColor('danger')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->badge($counts['bounced'])
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->bounced()),

            'complained' => Tab::make()
                ->label(__('Complained'))
                ->badgeColor('gray')
                ->icon('heroicon-o-face-frown')
                ->badge($counts['complained'])
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->complained()),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MailStatsWidget::class,
        ];
    }
}
