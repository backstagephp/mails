<div class="w-full h-screen">
    <iframe
        src="{{ route('filament.' . Filament\Facades\Filament::getCurrentPanel()->getId() . '.mails.preview', ['tenant' => Filament\Facades\Filament::getTenant(), 'mail' => $mail->id]) }}"
        class="w-full h-full max-w-full" style="width: 100vw; height: 100vh; border: none;">
    </iframe>
</div>
