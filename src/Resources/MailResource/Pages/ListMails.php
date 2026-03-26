<?php

namespace Backstage\Mails\Resources\MailResource\Pages;

use Backstage\Mails\Laravel\Models\Mail;
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

    public function mountAction(string $name, array $arguments = [], array $context = []): mixed
    {
        $result = parent::mountAction($name, $arguments, $context);

        if ($name === 'view' && isset($context['table']) && isset($context['recordKey'])) {
            $this->defaultTableAction = $name;
            $this->defaultTableActionRecord = $context['recordKey'];
        }

        return $result;
    }

    public function unmountAction(bool $canCancelParentActions = true): void
    {
        parent::unmountAction($canCancelParentActions);

        if (empty($this->mountedActions)) {
            $this->defaultTableAction = null;
            $this->defaultTableActionRecord = null;
        }
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
        /** @var Mail $class */
        $class = config('mails.models.mail');

        $class = new $class;

        return [
            'all' => Tab::make()
                ->label(__('All'))
                ->badgeColor('primary')
                ->icon('heroicon-o-inbox-stack')
                ->badge($class::count()),

            'unsent' => Tab::make()
                ->label(__('Unsent'))
                ->badgeColor('gray')
                ->icon('heroicon-o-inbox')
                ->badge($class::unsent()->count())
                ->modifyQueryUsing(function (Builder $query) use ($class): Builder {
                    return $class->unsent();
                }),

            'sent' => Tab::make()
                ->label(__('Sent'))
                ->badgeColor('info')
                ->icon('heroicon-o-paper-airplane')
                ->badge($class::sent()->count())
                ->modifyQueryUsing(function (Builder $query) use ($class): Builder {
                    return $class->sent();
                }),

            'delivered' => Tab::make()
                ->label(__('Delivered'))
                ->badgeColor('success')
                ->icon('heroicon-o-inbox-arrow-down')
                ->badge($class::delivered()->count())
                ->modifyQueryUsing(function (Builder $query) use ($class): Builder {
                    return $class->delivered();
                }),

            'opened' => Tab::make()
                ->label(__('Opened'))
                ->badgeColor('info')
                ->icon('heroicon-o-envelope-open')
                ->badge($class::opened()->count())
                ->modifyQueryUsing(function (Builder $query) use ($class): Builder {
                    return $class->opened();
                }),

            'clicked' => Tab::make()
                ->label(__('Clicked'))
                ->badgeColor('clicked')
                ->icon('heroicon-o-cursor-arrow-rays')
                ->badge($class::clicked()->count())
                ->modifyQueryUsing(function (Builder $query) use ($class): Builder {
                    return $class->clicked();
                }),

            'bounced' => Tab::make()
                ->label(__('Bounced'))
                ->badgeColor('danger')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->badge(fn () => $class::softBounced()->count() + $class::hardBounced()->count())
                ->modifyQueryUsing(function (Builder $query) use ($class): Builder {
                    return $query->where(function (Builder $subQuery) use ($class) {
                        return $subQuery->whereIn('id', $class::softBounced()->select('id'))
                            ->orWhereIn('id', $class::hardBounced()->select('id'));
                    });
                }),

            'complained' => Tab::make()
                ->label(__('Complained'))
                ->badgeColor('gray')
                ->icon('heroicon-o-face-frown')
                ->badge($class::complained()->count())
                ->modifyQueryUsing(function (Builder $query) use ($class): Builder {
                    return $class->complained();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MailStatsWidget::class,
        ];
    }
}
