<?php

use Backstage\Mails\Laravel\Models\Mail;
use Backstage\Mails\MailsPlugin;
use Backstage\Mails\Tests\Fixtures\Team;
use Backstage\Mails\Tests\Fixtures\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function tenantUser(): User
{
    return User::create([
        'name' => 'Tenant User',
        'email' => 'tenant@example.com',
        'password' => bcrypt('password'),
    ]);
}

function tenantAttachmentFor(Mail $mail, string $filename, string $contents)
{
    $attachment = $mail->attachments()->create([
        'uuid' => (string) Str::uuid(),
        'disk' => 'local',
        'filename' => $filename,
        'mime' => 'application/pdf',
        'inline' => false,
        'size' => strlen($contents),
    ]);

    Storage::disk('local')->put($attachment->storagePath, $contents);

    return $attachment;
}

it('resolves the mail parameter behind a tenant segment', function () {
    Storage::fake('local');

    $team = Team::create(['name' => 'Acme']);
    $mail = Mail::factory()->create(['html' => '<p>tenant body</p>']);
    $attachment = tenantAttachmentFor($mail, 'invoice.pdf', 'TENANT INVOICE');

    filament()->setCurrentPanel('tenancy');
    MailsPlugin::get()->canManageMails(true);

    $downloadUrl = route('filament.tenancy.mails.attachment.download', [
        'tenant' => $team,
        'mail' => $mail->id,
        'attachment' => $attachment->id,
        'filename' => $attachment->filename,
    ]);

    // The URL must actually carry the tenant segment, otherwise this proves nothing.
    expect($downloadUrl)->toContain('/tenancy/' . $team->getKey() . '/mails/');

    $response = $this->actingAs(tenantUser())->get($downloadUrl)->assertOk();

    expect($response->streamedContent())->toBe('TENANT INVOICE');
});

it('still scopes attachments to their mail behind a tenant segment', function () {
    Storage::fake('local');

    $team = Team::create(['name' => 'Acme']);
    $mail = Mail::factory()->create();
    $otherMail = Mail::factory()->create();
    $attachmentOfOtherMail = tenantAttachmentFor($otherMail, 'secret.pdf', 'CONFIDENTIAL');

    filament()->setCurrentPanel('tenancy');
    MailsPlugin::get()->canManageMails(true);

    $url = route('filament.tenancy.mails.attachment.download', [
        'tenant' => $team,
        'mail' => $mail->id,
        'attachment' => $attachmentOfOtherMail->id,
        'filename' => $attachmentOfOtherMail->filename,
    ]);

    $this->actingAs(tenantUser())->get($url)->assertNotFound();
});

it('previews a mail behind a tenant segment', function () {
    $team = Team::create(['name' => 'Acme']);
    $mail = Mail::factory()->create(['html' => '<p>tenant body</p>']);

    filament()->setCurrentPanel('tenancy');
    MailsPlugin::get()->canManageMails(true);

    $url = route('filament.tenancy.mails.preview', [
        'tenant' => $team,
        'mail' => $mail->id,
    ]);

    $this->actingAs(tenantUser())
        ->get($url)
        ->assertOk()
        ->assertSee('tenant body');
});

it('redirects a guest away from the tenant preview route', function () {
    $team = Team::create(['name' => 'Acme']);
    $mail = Mail::factory()->create(['html' => '<p>tenant body</p>']);

    $url = route('filament.tenancy.mails.preview', [
        'tenant' => $team,
        'mail' => $mail->id,
    ]);

    $this->get($url)->assertRedirect(route('filament.tenancy.auth.login'));
});
