@php
    $fieldId = $id ?? ('pwd-'.substr(str_replace('.', '', uniqid('', true)), -8));
@endphp
@once('password-toggle-assets')
    <link rel="stylesheet" href="{{ asset('css/password-toggle.css') }}">
@endonce
<div class="password-toggle-wrap">
    <input
        id="{{ $fieldId }}"
        type="password"
        class="form-control password-toggle-wrap__input {{ $inputClass ?? '' }}"
        name="{{ $name }}"
        @if(!empty($required)) required @endif
        autocomplete="{{ $autocomplete ?? 'current-password' }}"
        @if(isset($placeholder)) placeholder="{{ $placeholder }}" @endif
        @if(!empty($value)) value="{{ $value }}" @endif
    >
    <button
        type="button"
        class="password-toggle-wrap__btn"
        data-password-toggle
        aria-label="{{ __('Show password') }}"
        aria-pressed="false"
        tabindex="-1"
    >
        <i class="bi bi-eye" data-icon-show aria-hidden="true"></i>
        <i class="bi bi-eye-slash d-none" data-icon-hide aria-hidden="true"></i>
    </button>
</div>

@once('password-toggle-script')
@push('scripts')
<script>
(function () {
    function initPasswordToggles(root) {
        (root || document).querySelectorAll('[data-password-toggle]').forEach(function (btn) {
            if (btn.dataset.ptBound) return;
            btn.dataset.ptBound = '1';
            var wrap = btn.closest('.password-toggle-wrap');
            var input = wrap && wrap.querySelector('.password-toggle-wrap__input');
            if (!input) return;
            var iconShow = btn.querySelector('[data-icon-show]');
            var iconHide = btn.querySelector('[data-icon-hide]');
            var showLabel = @json(__('Show password'));
            var hideLabel = @json(__('Hide password'));

            btn.addEventListener('click', function () {
                var isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                btn.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                btn.setAttribute('aria-label', isHidden ? hideLabel : showLabel);
                if (iconShow) iconShow.classList.toggle('d-none', isHidden);
                if (iconHide) iconHide.classList.toggle('d-none', !isHidden);
            });
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { initPasswordToggles(); });
    } else {
        initPasswordToggles();
    }
})();
</script>
@endpush
@endonce
