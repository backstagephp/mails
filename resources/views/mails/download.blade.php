@php
    $attachment = $getState();
    $mailId = is_object($attachment) ? $attachment->mail_id : null;
@endphp

@if($mailId && $attachment)
<a type="button"
    href="{{ route('filament.' . Filament\Facades\Filament::getCurrentPanel()->getId() . '.mails.attachment.download', [
        'tenant' => Filament\Facades\Filament::getTenant(),
        'mail' => $mailId,
        'attachment' => $attachment->id,
        'filename' => $attachment->filename,
    ]) }}"
    class="rounded-md bg-white px-3.5 py-2.5 text-sm font-semibold cursor-pointer text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">Download</a>
@endif
