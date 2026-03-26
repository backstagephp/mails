<div class="w-full" x-data="{ height: '250px' }" x-on:message.window="
    if ($event.data && $event.data.type === 'mails-iframe-resize' && $event.data.mailId === '{{ $mail->id }}') {
        height = $event.data.height + 'px';
    }
">
    <iframe
        src="{{ route('filament.' . Filament\Facades\Filament::getCurrentPanel()->getId() . '.mails.preview', ['tenant' => Filament\Facades\Filament::getTenant(), 'mail' => $mail->id]) }}"
        class="w-full border-none"
        x-bind:style="'height: ' + height">
    </iframe>
</div>
