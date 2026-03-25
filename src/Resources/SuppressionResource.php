<?php

namespace Backstage\Mails\Resources;

use Backstage\Mails\Laravel\Enums\EventType;
use Backstage\Mails\Laravel\Enums\Provider;
use Backstage\Mails\Laravel\Events\MailUnsuppressed;
use Backstage\Mails\Laravel\Models\MailEvent;
use Backstage\Mails\MailsPlugin;
use Backstage\Mails\Resources\SuppressionResource\Pages\ListSuppressions;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SuppressionResource extends Resource
{
    protected static bool $isScopedToTenant = false;

    protected static bool $shouldRegisterNavigation = true;

    public static function canAccess(): bool
    {
        return MailsPlugin::get()->userCanManageMails();
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return config('mails.resources.mail')::getSlug() . '/suppressions';
    }

    public static function getModel(): string
    {
        return config('mails.models.event');
    }

    public function getTitle(): string
    {
        return __('Suppressions');
    }

    public static function getNavigationParentItem(): ?string
    {
        return config('mails.resources.mail')::getNavigationLabel();
    }

    public static function getNavigationGroup(): ?string
    {
        return config('mails.resources.mail')::getNavigationGroup();
    }

    public static function getNavigationLabel(): string
    {
        return __('Suppressions');
    }

    public static function getLabel(): ?string
    {
        return __('Suppression');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Suppressions');
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-no-symbol';
    }

    public static function getEloquentQuery(): Builder
    {
        $mailTable = config('mails.database.tables.mails');
        $eventTable = config('mails.database.tables.events');

        return parent::getEloquentQuery()
            ->join($mailTable, "$eventTable.mail_id", '=', "$mailTable.id")
            ->where(function ($query) use ($eventTable) {
                $query->where("$eventTable.type", EventType::HARD_BOUNCED)
                    ->orWhere("$eventTable.type", EventType::COMPLAINED);
            })
            ->whereNull("$eventTable.unsuppressed_at")
            ->whereIn("$mailTable.to", function ($query) use ($eventTable) {
                $query->select('to')
                    ->from($eventTable)
                    ->where('type', EventType::HARD_BOUNCED)
                    ->whereNull('unsuppressed_at')
                    ->groupBy('to');
            })
            ->select("$eventTable.*", "$mailTable.to")
            ->addSelect([
                'has_complained' => MailEvent::select('m.id')
                    ->from("$eventTable as me")
                    ->leftJoin("$mailTable as m", function ($join) {
                        $join->on('me.mail_id', '=', 'm.id')
                            ->where('me.type', '=', EventType::COMPLAINED);
                    })
                    ->take(1),
            ])
            ->latest("$eventTable.occurred_at");
    }

    public static function table(Table $table): Table
    {
        $eventTable = config('mails.database.tables.events');

        return $table
            ->defaultSort("$eventTable.occurred_at", 'desc')
            ->columns([
                TextColumn::make('to')
                    ->label(__('Email address'))
                    ->formatStateUsing(fn ($record) => key(json_decode($record->to ?? [])))
                    ->searchable(['to']),

                TextColumn::make('id')
                    ->label(__('Reason'))
                    ->badge()
                    ->formatStateUsing(fn ($record) => $record->type->value == EventType::COMPLAINED->value ? 'Complained' : 'Bounced')
                    ->color(fn ($record): string => match ($record->type->value == EventType::COMPLAINED->value) {
                        true => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('occurred_at')
                    ->label(__('Occurred At'))
                    ->dateTime('d-m-Y H:i')
                    ->since()
                    ->tooltip(fn (MailEvent $record) => $record->occurred_at->format('d-m-Y H:i'))
                    ->sortable()
                    ->searchable(),
            ])
            ->recordActions([
                Action::make('unsuppress')
                    ->label(__('Unsuppress'))
                    ->action(function (MailEvent $record) {
                        event(new MailUnsuppressed(key($record->mail->to), $record->mail->mailer == 'smtp' && filled($record->mail->transport) ? $record->mail->transport : $record->mail->mailer, $record->mail->stream_id ?? null));
                    })
                    ->visible(fn ($record) => Provider::tryFrom($record->mail->mailer == 'smtp' && filled($record->mail->transport) ? $record->mail->transport : $record->mail->mailer)),

                ViewAction::make()
                    ->url(null)
                    ->modal()
                    ->slideOver()
                    ->label(__('View'))
                    ->hiddenLabel()
                    ->tooltip(__('View'))
                    ->schema(fn (Schema $schema) => EventResource::infolist($schema)),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSuppressions::route('/'),
        ];
    }
}
