@php
    $uuid = $getState();
    $attachmentModel = config('mails.models.attachment', \Backstage\Mails\Laravel\Models\MailAttachment::class);
    $attachment = $uuid ? $attachmentModel::where('uuid', $uuid)->first() : null;
    $mailId = $attachment?->mail_id;
@endphp

@if($mailId && $attachment)
<a href="{{ route('filament.' . Filament\Facades\Filament::getCurrentPanel()->getId() . '.mails.attachment.download', [
        'tenant' => Filament\Facades\Filament::getTenant(),
        'mail' => $mailId,
        'attachment' => $attachment->id,
        'filename' => $attachment->filename,
    ]) }}"
    class="fi-btn fi-btn-size-sm inline-flex items-center justify-center gap-1 rounded-lg bg-primary-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 dark:bg-primary-500 dark:hover:bg-primary-400">
    <x-filament::icon icon="heroicon-m-arrow-down-tray" class="h-4 w-4" />
    Download
</a>
@endif
