<?php

namespace Backstage\Mails\Resources;

use Backstage\Mails\Laravel\Enums\EventType;
use Backstage\Mails\Laravel\Models\MailEvent;
use Backstage\Mails\MailsPlugin;
use Backstage\Mails\Resources\EventResource\Pages\ListEvents;
use Backstage\Mails\Resources\EventResource\Pages\ViewEvent;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventResource extends Resource
{
    protected static bool $isScopedToTenant = false;

    protected static bool $shouldRegisterNavigation = true;

    public static function canAccess(): bool
    {
        return MailsPlugin::get()->userCanManageMails();
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return config('mails.resources.mail')::getSlug() . '/events';
    }

    public function getTitle(): string
    {
        return __('Events');
    }

    public static function getModel(): string
    {
        return config('mails.models.event');
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
        return __('Events');
    }

    public static function getLabel(): ?string
    {
        return __('Event');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Events');
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-calendar';
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Event Details'))
                    ->icon('heroicon-o-information-circle')
                    ->compact()
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('type')
                                    ->label(__('Type'))
                                    ->badge()
                                    ->color(fn (EventType $state): string => match ($state) {
                                        EventType::DELIVERED => 'success',
                                        EventType::CLICKED => 'clicked',
                                        EventType::OPENED => 'info',
                                        EventType::SOFT_BOUNCED => 'danger',
                                        EventType::HARD_BOUNCED => 'danger',
                                        EventType::COMPLAINED => 'warning',
                                        EventType::UNSUBSCRIBED => 'danger',
                                        EventType::ACCEPTED => 'success',
                                    })
                                    ->formatStateUsing(function (EventType $state) {
                                        return ucwords(str_replace('_', ' ', $state->value));
                                    }),
                                TextEntry::make('mail.subject')
                                    ->label(__('Mail')),
                                TextEntry::make('occurred_at')
                                    ->since()
                                    ->dateTimeTooltip('d-m-Y H:i')
                                    ->label(__('Occurred At')),
                            ]),

                    ]),
                Section::make(__('User Information'))
                    ->icon('heroicon-o-user-circle')
                    ->compact()
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('ip_address')
                                    ->default(__('Unknown'))
                                    ->label(__('IP Address')),
                                TextEntry::make('hostname')
                                    ->default(__('Unknown'))
                                    ->label(__('Hostname')),
                                TextEntry::make('platform')
                                    ->default(__('Unknown'))
                                    ->label(__('Platform')),
                                TextEntry::make('os')
                                    ->default(__('Unknown'))
                                    ->label(__('Operating System')),
                                TextEntry::make('browser')
                                    ->default(__('Unknown'))
                                    ->label(__('Browser')),
                                TextEntry::make('user_agent')
                                    ->default(__('Unknown'))
                                    ->label(__('User Agent'))
                                    ->limit(50)
                                    ->tooltip(fn ($state) => $state),
                            ]),
                    ]),
                Section::make(__('Location'))
                    ->icon('heroicon-o-map-pin')
                    ->compact()
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('city')
                                    ->default(__('Unknown'))
                                    ->label(__('City')),
                                TextEntry::make('country_code')
                                    ->default(__('Unknown'))
                                    ->label(__('Country Code')),
                            ]),
                    ]),
                Section::make(__('Additional Information'))
                    ->icon('heroicon-o-information-circle')
                    ->compact()
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('link')
                                    ->default(__('Unknown'))
                                    ->label(__('Link'))
                                    ->limit(50)
                                    ->url(fn ($state) => $state)
                                    ->openUrlInNewTab(),
                                TextEntry::make('tag')
                                    ->default(__('Unknown'))
                                    ->label(__('Tag')),
                                TextEntry::make('payload')
                                    ->label(__('Payload'))
                                    ->formatStateUsing(function ($state) {
                                        return json_encode($state, JSON_PRETTY_PRINT);
                                    })
                                    ->columnSpanFull()
                                    ->copyable()
                                    ->copyMessage(__('Copied'))
                                    ->copyMessageDuration(1500),
                                TextEntry::make('payload')
                                    ->label(__('Metadata'))
                                    ->formatStateUsing(function ($state) {
                                        if (
                                            ! is_object($state) || ! property_exists($state, 'Metadata') || empty($state->Metadata)
                                        ) {
                                            return __('No metadata available');
                                        }

                                        $metadata = (array) json_decode(json_encode($state->Metadata), true);
                                        unset($metadata[config('mails.headers.uuid')]);

                                        return json_encode($metadata, JSON_PRETTY_PRINT);
                                    })
                                    ->columnSpanFull()
                                    ->copyable()
                                    ->copyMessage(__('Copied'))
                                    ->copyMessageDuration(1500),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordAction('view')
            ->recordUrl(null)
            ->defaultSort('occurred_at', 'desc')
            ->paginated([50, 100, 'all'])
            ->columns([
                TextColumn::make('type')
                    ->label(__('Type'))
                    ->sortable()
                    ->badge()
                    ->color(fn (EventType $state): string => match ($state) {
                        EventType::DELIVERED => 'success',
                        EventType::CLICKED => 'clicked',
                        EventType::OPENED => 'info',
                        EventType::SOFT_BOUNCED => 'danger',
                        EventType::HARD_BOUNCED => 'danger',
                        EventType::COMPLAINED => 'warning',
                        EventType::UNSUBSCRIBED => 'danger',
                        EventType::ACCEPTED => 'success',
                    })
                    ->formatStateUsing(function (EventType $state) {
                        return ucwords(str_replace('_', ' ', $state->value));
                    })
                    ->searchable(),
                TextColumn::make('mail.subject')
                    ->label(__('Subject'))
                    ->searchable(['subject', 'payload']),
                TextColumn::make('occurred_at')
                    ->label(__('Occurred At'))
                    ->dateTime('d-m-Y H:i')
                    ->since()
                    ->tooltip(fn (MailEvent $record) => $record->occurred_at->format('d-m-Y H:i'))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(null)
                    ->modal()
                    ->slideOver()
                    ->label(__('View'))
                    ->hiddenLabel()
                    ->tooltip(__('View')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEvents::route('/'),
            'view' => ViewEvent::route('/{record}/view'),
        ];
    }
}
