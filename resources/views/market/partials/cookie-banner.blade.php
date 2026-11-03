{{-- Cookie Consent Banner --}}
<div id="cbCookie" class="cb-cookie" hidden>
    <span>{{ __('We use cookies to improve your experience.') }} <a href="{{ route('legal.privacy') }}"
            class="text-decoration-underline">{{ __('Privacy') }}</a></span>
    <button type="button" class="cb-btn-gold btn-sm" id="cbCookieOk">{{ __('Accept') }}</button>
</div>

<script>
    (function () {
        var bar = document.getElementById('cbCookie');
        if (!bar) return;
        if (!localStorage.getItem('cb_cookie_ok')) bar.removeAttribute('hidden');
        document.getElementById('cbCookieOk')?.addEventListener('click', function () {
            localStorage.setItem('cb_cookie_ok', '1');
            bar.setAttribute('hidden', '');
        });
    })();
</script>
