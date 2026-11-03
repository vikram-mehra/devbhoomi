{{-- Holiday / Diwali Notice Popup Modal --}}
@if(config('holiday.enabled'))
<div class="modal fade" id="holidayModal" tabindex="-1" aria-labelledby="holidayModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content pro-holiday-popup border-0 shadow-lg position-relative">
            <div class="modal-body text-center position-relative">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3 js-holiday-dismiss"
                    data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>

                @if(config('holiday.show_diya', true))
                    <!-- Animated Diwali Diya -->
                    <div class="diwali-diya-wrap">
                        <svg class="diwali-diya-svg" viewBox="0 0 120 100" width="100" height="85" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <radialGradient id="flameGlow" cx="50%" cy="50%" r="50%">
                                    <stop offset="0%" stop-color="#ffb703" stop-opacity="0.85"/>
                                    <stop offset="100%" stop-color="#fb8500" stop-opacity="0"/>
                                </radialGradient>
                                <linearGradient id="flameGrad" x1="0%" y1="100%" x2="0%" y2="0%">
                                    <stop offset="0%" stop-color="#d90429"/>
                                    <stop offset="30%" stop-color="#f77f00"/>
                                    <stop offset="70%" stop-color="#fcbf49"/>
                                    <stop offset="100%" stop-color="#fff3b0"/>
                                </linearGradient>
                                <linearGradient id="flameInner" x1="0%" y1="100%" x2="0%" y2="0%">
                                    <stop offset="0%" stop-color="#f77f00"/>
                                    <stop offset="50%" stop-color="#fee440"/>
                                    <stop offset="100%" stop-color="#ffffff"/>
                                </linearGradient>
                                <linearGradient id="diyaClay" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" stop-color="#e76f51"/>
                                    <stop offset="50%" stop-color="#b54425"/>
                                    <stop offset="100%" stop-color="#7f230f"/>
                                </linearGradient>
                                <linearGradient id="diyaRim" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#f4a261"/>
                                    <stop offset="50%" stop-color="#ffd166"/>
                                    <stop offset="100%" stop-color="#f4a261"/>
                                </linearGradient>
                                <filter id="diyaBloom" x="-40%" y="-40%" width="180%" height="180%">
                                    <feGaussianBlur stdDeviation="3" result="blur"/>
                                    <feComposite in="SourceGraphic" in2="blur" operator="over"/>
                                </filter>
                            </defs>

                            <!-- Ambient glow behind flame -->
                            <circle cx="60" cy="30" r="28" fill="url(#flameGlow)" class="diwali-flame-halo" />

                            <!-- Sparkles -->
                            <circle cx="30" cy="18" r="1.5" fill="#ffd166" class="diwali-sparkle s1" />
                            <circle cx="90" cy="20" r="1.5" fill="#ffd166" class="diwali-sparkle s2" />
                            <circle cx="22" cy="34" r="1.2" fill="#ffb703" class="diwali-sparkle s3" />
                            <circle cx="98" cy="32" r="1.2" fill="#ffb703" class="diwali-sparkle s4" />

                            <!-- Flame group (animated) -->
                            <g class="diwali-flame-group" filter="url(#diyaBloom)">
                                <path d="M 60 4 C 68 20 75 32 69 42 C 65 48 55 48 51 42 C 45 32 52 20 60 4 Z" fill="url(#flameGrad)" />
                                <path d="M 60 13 C 64 22 69 30 65 38 C 63 42 57 42 55 38 C 51 30 56 22 60 13 Z" fill="url(#flameInner)" />
                                <ellipse cx="60" cy="34" rx="2" ry="4" fill="#ffffff" opacity="0.95" />
                            </g>

                            <!-- Wick -->
                            <path d="M 60 42 Q 60 46 61 50" stroke="#2b2d42" stroke-width="2.2" stroke-linecap="round" fill="none" />

                            <!-- Diya Base Shadow -->
                            <ellipse cx="60" cy="85" rx="36" ry="6" fill="#000000" opacity="0.12" />

                            <!-- Main clay body -->
                            <path d="M 18 50 C 20 74 40 82 60 82 C 80 82 100 74 102 50 C 102 48 16 48 18 50 Z" fill="url(#diyaClay)" />

                            <!-- Diya Rim -->
                            <ellipse cx="60" cy="50" rx="42" ry="7" fill="url(#diyaRim)" />
                            <ellipse cx="60" cy="51" rx="36" ry="4.5" fill="#5c1d0c" />

                            <!-- Traditional Dots on rim -->
                            <g fill="#ffe6a7" opacity="0.95">
                                <circle cx="30" cy="51" r="1" />
                                <circle cx="38" cy="52" r="1.2" />
                                <circle cx="48" cy="52.5" r="1.4" />
                                <circle cx="60" cy="53" r="1.5" />
                                <circle cx="72" cy="52.5" r="1.4" />
                                <circle cx="82" cy="52" r="1.2" />
                                <circle cx="90" cy="51" r="1" />
                            </g>
                            <path d="M 38 65 Q 60 74 82 65" stroke="#ffd166" stroke-width="1.2" stroke-linecap="round" fill="none" opacity="0.8" />
                        </svg>
                    </div>
                @else
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-10 text-warning mb-3" style="width: 72px; height: 72px;">
                        <i class="bi bi-calendar2-heart-fill fs-1 text-warning"></i>
                    </div>
                @endif

                <div class="mb-2">
                    <span class="badge text-dark fw-bold px-3 py-1 rounded-pill text-uppercase shadow-sm pro-holiday-popup__badge">
                        ✨ {{ __('Festival Wishes') }} ✨
                    </span>
                </div>
                <h2 class="h4 fw-bold mb-2 pro-holiday-popup__title" id="holidayModalLabel">
                    {{ config('holiday.title', __('Happy Diwali from Devbhoomi Naturals! 🪔')) }}
                </h2>
                <p class="text-muted small mb-4 lh-base pro-holiday-popup__desc">
                    {{ config('holiday.message') }}
                </p>
                <div class="bg-white p-3 rounded-3 mb-4 text-start small border shadow-sm">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-check-circle-fill text-success fs-6"></i>
                        <span><strong>{{ __('Catalog & Cart:') }}</strong> {{ __('Browse products & add to cart anytime') }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 text-muted">
                        <i class="bi bi-pause-circle-fill text-warning fs-6"></i>
                        <span><strong>{{ __('Checkout & Orders:') }}</strong> {{ __('Temporarily paused during Diwali break') }}</span>
                    </div>
                </div>
                <button type="button" class="btn btn-primary rounded-pill px-4 py-2 w-100 fw-semibold js-holiday-dismiss shadow-sm" data-bs-dismiss="modal">
                    {{ __('Understood, Continue Browsing') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var STORAGE_KEY = 'zm_holiday_popup_dismissed_until';
        var ONE_HOUR_MS = 60 * 60 * 1000;
        var holidayEl = document.getElementById('holidayModal');
        if (!holidayEl) return;

        function dismissHoliday() {
            try {
                localStorage.setItem(STORAGE_KEY, String(Date.now() + ONE_HOUR_MS));
            } catch (e) {}
            document.body.classList.remove('holiday-modal-open');
        }

        holidayEl.querySelectorAll('.js-holiday-dismiss').forEach(function (btn) {
            btn.addEventListener('click', dismissHoliday);
        });
        holidayEl.addEventListener('show.bs.modal', function () {
            document.body.classList.add('holiday-modal-open');
        });
        holidayEl.addEventListener('hidden.bs.modal', dismissHoliday);

        var dismissedUntil = null;
        try {
            dismissedUntil = localStorage.getItem(STORAGE_KEY);
        } catch (e) {}

        var now = Date.now();
        if (!dismissedUntil || now >= parseInt(dismissedUntil, 10)) {
            setTimeout(function () {
                if (window.bootstrap && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(holidayEl).show();
                }
            }, 600);
        }
    })();
</script>
@endif
