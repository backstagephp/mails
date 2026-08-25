<div class="w-full h-screen">
    <iframe
        srcdoc="{{ $html }}"
        sandbox
        referrerpolicy="no-referrer"
        title="{{ __('Email preview') }}"
        class="w-full h-full max-w-full" style="width: 100vw; height: 100vh; border: none;">
    </iframe>
</div>
