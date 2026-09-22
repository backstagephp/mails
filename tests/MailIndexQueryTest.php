<?php

use Backstage\Mails\Laravel\Models\Mail;
use Backstage\Mails\MailsPlugin;
use Backstage\Mails\Resources\MailResource;
use Backstage\Mails\Resources\MailResource\Pages\ListMails;
use Backstage\Mails\Tests\Fixtures\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    Filament::setCurrentPanel('admin');

    MailsPlugin::get()->canManageMails(true);

    config()->set('mails.cache.counts_ttl', 0);

    $this->actingAs(User::create([
        'name' => 'Test User',
        'email' => 'index@example.com',
        'password' => Hash::make('password'),
    ]));
});

function mailIndex(?string $activeTab = null, ?string $search = null): ListMails
{
    $page = new ListMails;
    $page->activeTab = $activeTab;
    $page->tableSearch = $search;
    $page->bootedInteractsWithTable();

    return $page;
}

function searchMails(string $search): array
{
    return mailIndex(search: $search)->getTableRecords()->pluck('subject')->sort()->values()->all();
}

it('leaves the mail bodies out of the index query', function () {
    Mail::factory()->create([
        'subject' => 'Hello',
        'html' => '<p>body</p>',
        'text' => 'body',
    ]);

    $record = mailIndex()->getTableRecords()->first();

    expect($record->getAttributes())
        ->toHaveKey('subject')
        ->not->toHaveKey('html')
        ->not->toHaveKey('text');
});

it('keeps the mail bodies out of the index query on a filtered tab', function () {
    Mail::factory()->create([
        'html' => '<p>body</p>',
        'hard_bounced_at' => now(),
    ]);

    $record = mailIndex('bounced')->getTableRecords()->first();

    expect($record->getAttributes())->not->toHaveKey('html');
});

it('loads the full mail when resolving a single record for the view modal', function () {
    $mail = Mail::factory()->create(['html' => '<p>body</p>']);

    $record = mailIndex()->getTableRecord((string) $mail->getKey());

    expect($record->html)->toBe('<p>body</p>');
});

it('lists both soft and hard bounces under the bounced tab', function () {
    $soft = Mail::factory()->create(['soft_bounced_at' => now()]);
    $hard = Mail::factory()->create(['hard_bounced_at' => now()]);
    Mail::factory()->create();

    $records = mailIndex('bounced')->getTableRecords();

    expect($records->pluck('id')->all())
        ->toEqualCanonicalizing([$soft->getKey(), $hard->getKey()]);
});

it('counts unsent mails as the mails that have no sent_at', function () {
    Mail::factory()->count(2)->create(['sent_at' => now()]);
    Mail::factory()->count(3)->create(['sent_at' => null]);

    expect(MailResource::getStatusCounts())
        ->toMatchArray([
            'all' => 5,
            'sent' => 2,
            'unsent' => 3,
        ]);
});

it('finds mails by recipient address, name and domain', function () {
    Mail::factory()->create([
        'subject' => 'To Mark',
        'to' => ['Mark@UX.nl' => 'Mark van Eijk'],
    ]);
    Mail::factory()->create([
        'subject' => 'Cc Jane',
        'to' => ['someone@example.com' => null],
        'cc' => ['jane@example.org' => 'Jane Doe'],
    ]);
    Mail::factory()->create([
        'subject' => 'Unrelated',
        'to' => ['other@example.net' => 'Other'],
    ]);

    expect(searchMails('mark@ux.nl'))->toBe(['To Mark'])
        ->and(searchMails('mark'))->toBe(['To Mark'])
        ->and(searchMails('@ux.nl'))->toBe(['To Mark'])
        ->and(searchMails('Jane'))->toBe(['Cc Jane'])
        ->and(searchMails('example.org'))->toBe(['Cc Jane'])
        ->and(searchMails('nobody'))->toBe([]);
});

it('finds mails by subject and body alongside recipient matches', function () {
    Mail::factory()->create([
        'subject' => 'Your invoice',
        'to' => ['a@example.com' => null],
    ]);
    Mail::factory()->create([
        'subject' => 'Welcome',
        'text' => 'Please find the invoice attached',
        'to' => ['b@example.com' => null],
    ]);
    Mail::factory()->create([
        'subject' => 'Statement',
        'to' => ['invoices@example.com' => null],
    ]);
    Mail::factory()->create([
        'subject' => 'Hello',
        'to' => ['c@example.com' => null],
    ]);

    expect(searchMails('invoice'))->toBe(['Statement', 'Welcome', 'Your invoice']);
});
