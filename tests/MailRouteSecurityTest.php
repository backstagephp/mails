<?php

use Backstage\Mails\Laravel\Models\Mail;
use Backstage\Mails\MailsPlugin;
use Backstage\Mails\Tests\Fixtures\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function mailUser(): User
{
    return User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);
}

function attachmentFor(Mail $mail, string $filename = 'invoice.pdf', string $contents = 'CONFIDENTIAL')
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

function previewUrl(Mail $mail): string
{
    return route('filament.admin.mails.preview', ['mail' => $mail->id]);
}

function downloadUrl(Mail $mail, $attachment): string
{
    return route('filament.admin.mails.attachment.download', [
        'mail' => $mail->id,
        'attachment' => $attachment->id,
        'filename' => $attachment->filename,
    ]);
}

it('redirects a guest away from the mail preview', function () {
    $mail = Mail::factory()->create(['html' => '<p>secret</p>']);

    $this->get(previewUrl($mail))
        ->assertRedirect(route('filament.admin.auth.login'));
});

it('forbids an authenticated user without mail permissions', function () {
    $mail = Mail::factory()->create(['html' => '<p>secret</p>']);

    MailsPlugin::get()->canManageMails(false);

    $this->actingAs(mailUser())
        ->get(previewUrl($mail))
        ->assertForbidden();
});

it('allows a permitted user to preview a mail', function () {
    $mail = Mail::factory()->create(['html' => '<p>secret</p>']);

    MailsPlugin::get()->canManageMails(true);

    $this->actingAs(mailUser())
        ->get(previewUrl($mail))
        ->assertOk()
        ->assertSee('secret');
});
