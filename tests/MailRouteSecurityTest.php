<?php

use Backstage\Mails\Laravel\Models\Mail;
use Backstage\Mails\Laravel\Models\MailAttachment;
use Backstage\Mails\MailsPlugin;
use Backstage\Mails\Tests\Fixtures\DenyOddMailPolicy;
use Backstage\Mails\Tests\Fixtures\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function mailUser(): User
{
    return User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);
}

function attachmentFor(Mail $mail, string $filename = 'invoice.pdf', string $contents = 'CONFIDENTIAL'): MailAttachment
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
    return route('filament.admin.mails.preview', ['mail' => $mail->getKey()]);
}

function downloadUrl(Mail $mail, MailAttachment $attachment): string
{
    return route('filament.admin.mails.attachment.download', [
        'mail' => $mail->getKey(),
        'attachment' => $attachment->getKey(),
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

it('allows a permitted user to preview a mail with hardening headers', function () {
    $mail = Mail::factory()->create(['html' => '<p>secret</p>']);

    MailsPlugin::get()->canManageMails(true);

    $this->actingAs(mailUser())
        ->get(previewUrl($mail))
        ->assertSuccessful()
        ->assertSee('secret')
        ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
        ->assertHeader('Content-Security-Policy', "sandbox; frame-ancestors 'self'")
        ->assertHeader('Referrer-Policy', 'no-referrer')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
});

it('returns not found for an unknown mail preview', function () {
    MailsPlugin::get()->canManageMails(true);

    $url = route('filament.admin.mails.preview', ['mail' => 99999]);

    $this->actingAs(mailUser())->get($url)->assertNotFound();
});

it('redirects a guest away from an attachment download', function () {
    Storage::fake('local');

    $mail = Mail::factory()->create();
    $attachment = attachmentFor($mail);

    $this->get(downloadUrl($mail, $attachment))
        ->assertRedirect(route('filament.admin.auth.login'));
});

it('forbids an authenticated user without mail permissions from downloading an attachment', function () {
    Storage::fake('local');

    $mail = Mail::factory()->create();
    $attachment = attachmentFor($mail);

    MailsPlugin::get()->canManageMails(false);

    $this->actingAs(mailUser())
        ->get(downloadUrl($mail, $attachment))
        ->assertForbidden();
});

it('does not serve an attachment belonging to a different mail', function () {
    Storage::fake('local');

    $mail = Mail::factory()->create();
    $otherMail = Mail::factory()->create();
    $attachmentOfOtherMail = attachmentFor($otherMail, 'secret.pdf');

    MailsPlugin::get()->canManageMails(true);

    $this->actingAs(mailUser())
        ->get(downloadUrl($mail, $attachmentOfOtherMail))
        ->assertNotFound();
});

it('serves an attachment that belongs to the mail', function () {
    Storage::fake('local');

    $mail = Mail::factory()->create();
    $attachment = attachmentFor($mail, 'invoice.pdf', 'INVOICE BODY');

    MailsPlugin::get()->canManageMails(true);

    $response = $this->actingAs(mailUser())
        ->get(downloadUrl($mail, $attachment))
        ->assertSuccessful();

    expect($response->streamedContent())->toBe('INVOICE BODY');
});

it('returns not found for an unknown attachment', function () {
    Storage::fake('local');

    $mail = Mail::factory()->create();

    MailsPlugin::get()->canManageMails(true);

    $url = route('filament.admin.mails.attachment.download', [
        'mail' => $mail->getKey(),
        'attachment' => 99999,
        'filename' => 'missing.pdf',
    ]);

    $this->actingAs(mailUser())->get($url)->assertNotFound();
});

it('renders stored html in a sandboxed inline preview', function () {
    $html = '<script>window.parent.document.body.remove()</script><p>Preview</p>';

    $preview = view('mails::mails.preview', ['html' => $html])->render();

    expect($preview)
        ->toContain('srcdoc="&lt;script&gt;')
        ->toContain('sandbox')
        ->toContain('referrerpolicy="no-referrer"')
        ->not->toContain('src="');
});

it('consults the host mail policy for the preview when one is registered', function () {
    Gate::policy(Mail::class, DenyOddMailPolicy::class);
    MailsPlugin::get()->canManageMails(true);

    $user = mailUser();
    $denied = Mail::factory()->create(['id' => 1, 'html' => '<p>secret</p>']);
    $allowed = Mail::factory()->create(['id' => 2, 'html' => '<p>visible</p>']);

    $this->actingAs($user)->get(previewUrl($denied))->assertForbidden();
    $this->actingAs($user)->get(previewUrl($allowed))->assertSuccessful()->assertSee('visible');
});

it('consults the host mail policy for attachment downloads when one is registered', function () {
    Storage::fake('local');
    Gate::policy(Mail::class, DenyOddMailPolicy::class);
    MailsPlugin::get()->canManageMails(true);

    $denied = Mail::factory()->create(['id' => 1]);
    $attachment = attachmentFor($denied);

    $this->actingAs(mailUser())->get(downloadUrl($denied, $attachment))->assertForbidden();
});
