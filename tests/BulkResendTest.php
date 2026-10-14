<?php

use Backstage\Mails\Laravel\Jobs\ResendMailJob;
use Backstage\Mails\Laravel\Models\Mail;
use Backstage\Mails\Resources\MailResource;
use Backstage\Mails\Resources\MailResource\Pages\ListMails;
use Backstage\Mails\Tests\Fixtures\User;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

function sentMail(array $to, array $cc = []): Mail
{
    return Mail::create([
        'uuid' => (string) Str::uuid(),
        'mailer' => 'smtp',
        'subject' => 'Your link',
        'to' => $to,
        'cc' => $cc,
        'html' => '<p>Your link</p>',
    ]);
}

function recipientsOf(ResendMailJob $job): array
{
    $read = fn (string $property) => (new ReflectionProperty($job, $property))->getValue($job);

    return ['to' => $read('to'), 'cc' => $read('cc')];
}

it('resends each selected mail to its own recipients only', function () {
    Queue::fake();
    $this->actingAs(User::create(['name' => 'Test User', 'email' => 'test@example.com', 'password' => bcrypt('password')]));

    $first = sentMail(['ann@one.test' => 'Ann'], ['boss@one.test' => null]);
    $second = sentMail(['bob@two.test' => 'Bob']);

    $resend = collect(MailResource::table(Table::make(new ListMails))->getFlatBulkActions())
        ->first(fn ($action) => $action->getName() === 'resend');

    $resend->evaluate($resend->getActionFunction(), ['records' => new Collection([$first, $second])]);

    $sent = [];
    Queue::assertPushed(ResendMailJob::class, function (ResendMailJob $job) use (&$sent) {
        $sent[] = recipientsOf($job);

        return true;
    });

    expect($sent)->toEqualCanonicalizing([
        ['to' => ['ann@one.test'], 'cc' => ['boss@one.test']],
        ['to' => ['bob@two.test'], 'cc' => []],
    ]);
});
