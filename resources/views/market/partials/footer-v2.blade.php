@php
    $dnProductsUrl = \App\Models\MenuItem::ourProductsUrl();
    $dnOurProducts = collect($layoutHeaderMenu ?? [])->first(function ($item) {
        $slug = strtolower(trim((string) ($item->slug ?? '')));
        $title = strtolower(trim((string) ($item->title ?? '')));

        return $slug === 'our-products' || $title === 'our products';
    });
    $dnProductMenus = collect();
    $dnProductItems = collect();
    if ($dnOurProducts) {
        $dnProductMenus = $dnOurProducts->children->where('is_active', true)->sortBy('sort_order')->values();
        if ($dnProductMenus->isEmpty() && !$dnOurProducts->isBuiltInPage()) {
            $dnProductItems = $dnOurProducts->dropdownProducts();
        }
    }
@endphp
<footer class="dn-ft" aria-label="{{ __('Site footer') }}">
    <div class="dn-ft__top">
        <div class="dn-ft__inner">
            <div class="dn-ft__brand">
                <a href="{{ route('market.home') }}" class="dn-ft__logo">
                    <img src="{{ asset('images/footer-logo-lockup.png') }}" alt="{{ config('app.name') }}" width="280"
                        height="90" decoding="async">
                </a>
                <p class="dn-ft__about">
                    {{ __('Bringing the true goodness of the Himalayas to your home. Our natural grains and pulses are carefully sourced, pure, and full of nutrition — just the way nature intended.') }}
                </p>
                <div class="dn-ft__scene">
                    <p class="dn-ft__goodness">
                        <span class="dn-ft__goodness-top">{{ __('Goodness') }}</span>
                        <span class="dn-ft__goodness-line">
                            {{ __('from the Himalayas') }}
                            <svg class="dn-ft__goodness-mark" viewBox="0 0 148 18" aria-hidden="true">
                                <path d="M4 11 C 38 4, 78 15, 118 8" fill="none" stroke="currentColor"
                                    stroke-width="1.8" stroke-linecap="round" />
                                <path d="M128 4.2c6 1.8 9.4 6.8 7.8 12.2-4.2-1.4-8.4-4-10.6-9.2 1.8.2 3.8.8 5.8 1.8z"
                                    fill="currentColor" />
                            </svg>
                        </span>
                    </p>
                </div>
            </div>

            <nav class="dn-ft__col" aria-label="{{ __('Quick Links') }}">
                <h3 class="dn-ft__h">{{ __('Quick Links') }}</h3>
                <ul>
                    <li><a href="{{ route('market.home') }}">{{ __('Home') }} <i class="bi bi-chevron-right"
                                aria-hidden="true"></i></a></li>
                    <li><a href="{{ $dnProductsUrl }}">{{ __('Our Products') }} <i class="bi bi-chevron-right"
                                aria-hidden="true"></i></a></li>
                    <li><a href="{{ route('pages.about') }}">{{ __('About Us') }} <i class="bi bi-chevron-right"
                                aria-hidden="true"></i></a></li>
                    <li><a href="{{ route('offers.index') }}">{{ __('Offers') }} <i class="bi bi-chevron-right"
                                aria-hidden="true"></i></a></li>
                    <li><a href="{{ route('blog.index') }}">{{ __('Blog') }} <i class="bi bi-chevron-right"
                                aria-hidden="true"></i></a></li>
                    <li><a href="{{ route('pages.contact') }}">{{ __('Contact Us') }} <i class="bi bi-chevron-right"
                                aria-hidden="true"></i></a></li>
                </ul>
            </nav>

            <nav class="dn-ft__col" aria-label="{{ __('Shop by Product') }}">
                <h3 class="dn-ft__h">{{ __('Shop by Product') }}</h3>
                <ul>
                    @forelse($dnProductMenus as $link)
                        <li>
                            <a href="{{ $link->resolvedUrl() }}" @if($link->target_blank) target="_blank"
                            rel="noopener noreferrer" @endif>{{ $link->title }} <i class="bi bi-chevron-right"
                                    aria-hidden="true"></i></a>
                        </li>
                    @empty
                        @forelse($dnProductItems as $prod)
                            <li>
                                <a href="{{ route('product.show', $prod) }}">{{ $prod->name }} <i class="bi bi-chevron-right"
                                        aria-hidden="true"></i></a>
                            </li>
                        @empty
                            <li>
                                <a href="{{ $dnProductsUrl }}">{{ __('Our Products') }} <i class="bi bi-chevron-right"
                                        aria-hidden="true"></i></a>
                            </li>
                        @endforelse
                    @endforelse
                </ul>
            </nav>

            <nav class="dn-ft__col" aria-label="{{ __('Customer Service') }}">
                <h3 class="dn-ft__h">{{ __('Customer Service') }}</h3>
                <ul>
                    <li><a href="{{ route('legal.shipping') }}">{{ __('Shipping Policy') }} <i
                                class="bi bi-chevron-right" aria-hidden="true"></i></a></li>
                    <li><a href="{{ route('legal.refund') }}">{{ __('Return & Refund') }} <i class="bi bi-chevron-right"
                                aria-hidden="true"></i></a></li>
                    <li><a href="{{ route('legal.privacy') }}">{{ __('Privacy Policy') }} <i class="bi bi-chevron-right"
                                aria-hidden="true"></i></a></li>
                    <li><a href="{{ route('legal.terms') }}">{{ __('Terms & Conditions') }} <i
                                class="bi bi-chevron-right" aria-hidden="true"></i></a></li>
                    <li><a href="{{ route('market.home') }}#faq-heading">{{ __('FAQs') }} <i class="bi bi-chevron-right"
                                aria-hidden="true"></i></a></li>
                </ul>
            </nav>

            <div class="dn-ft__col dn-ft__col--touch">
                <h3 class="dn-ft__h">{{ __('Get in Touch') }}</h3>
                <ul class="dn-ft__contact">
                    <li>
                        <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
                        <span>{{ __('Uttarakhand, India') }}</span>
                    </li>
                    <li>
                        <i class="bi bi-envelope-fill" aria-hidden="true"></i>
                        <a href="mailto:support@devbhoominaturals.com">support@devbhoominaturals.com</a>
                    </li>
                    <li>
                        <i class="bi bi-telephone-fill" aria-hidden="true"></i>
                        <a href="tel:+919217732670">+91 92177 32670</a>
                    </li>
                    <li>
                        <i class="bi bi-whatsapp" aria-hidden="true"></i>
                        <a href="https://wa.me/919217732670?text=Hi" target="_blank"
                            rel="noopener">{{ __('Chat on WhatsApp') }}</a>
                    </li>
                </ul>
                <h3 class="dn-ft__h dn-ft__h--follow">{{ __('Follow Us') }}</h3>
                <div class="dn-ft__social">
                    <a href="https://www.facebook.com/share/1D7KtFBEGi/?mibextid=wwXIfr"
                        class="pro-footer-mk__social-btn" aria-label="Facebook"><i class="bi bi-facebook"
                            aria-hidden="true"></i></a>
                    <a href="https://www.instagram.com/dev_bhoominaturals?igsh=MXJwZXYzeDQwamNjMw%3D%3D"
                        class="pro-footer-mk__social-btn" aria-label="Instagram" target="_blank" rel="noopener"><i
                            class="bi bi-instagram" aria-hidden="true"></i></a>
                    <a href="#" aria-label="YouTube"><i class="bi bi-youtube" aria-hidden="true"></i></a>
                    <a href="#" class="pro-footer-mk__social-btn" aria-label="Twitter"><i class="bi bi-twitter-x"
                            aria-hidden="true"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="dn-ft__trust">
        <div class="dn-ft__trust-inner">
            <ul class="dn-ft__points">
                <li>
                    <span class="dn-ft__point-icon" aria-hidden="true">
                        <img src="{{ asset('images/footer-leaf.png') }}?v=1" alt="" width="22" height="23">
                    </span>
                    <span><strong>{{ __('100% Natural') }}</strong><small>{{ __('No Artificial Additives') }}</small></span>
                </li>
                <li>
                    <span class="dn-ft__point-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                            stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3 4.5 6.2v5.3c0 4.6 3.2 8.7 7.5 9.7 4.3-1 7.5-5.1 7.5-9.7V6.2L12 3z" />
                            <path d="m8.8 12.2 2.2 2.2 4.3-4.4" />
                        </svg>
                    </span>
                    <span><strong>{{ __('Premium Quality') }}</strong><small>{{ __('Handpicked with Care') }}</small></span>
                </li>
                <li>
                    <span class="dn-ft__point-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                            stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 16V8h11l4 4h3v4" />
                            <path d="M7 16h.01M17 16h.01" />
                            <circle cx="7.5" cy="16.5" r="1.8" />
                            <circle cx="17.5" cy="16.5" r="1.8" />
                        </svg>
                    </span>
                    <span><strong>{{ __('Fast & Safe Delivery') }}</strong><small>{{ __('Across India') }}</small></span>
                </li>
                <li>
                    <span class="dn-ft__point-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                            stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="11" width="14" height="10" rx="2" />
                            <path d="M8 11V8a4 4 0 0 1 8 0v3" />
                        </svg>
                    </span>
                    <span><strong>{{ __('Secure Payments') }}</strong><small>{{ __('Multiple Options') }}</small></span>
                </li>
                <li>
                    <span class="dn-ft__point-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor"
                            stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z" />
                        </svg>
                    </span>
                    <span><strong>{{ __('Healthy Living') }}</strong><small>{{ __('Our Promise') }}</small></span>
                </li>
            </ul>
            <div class="dn-ft__pay" aria-label="{{ __('Payment methods') }}">
                <img src="{{ asset('images/payments/payment-cards.png') }}" alt="UPI, Visa, Mastercard, RuPay"
                    width="320" height="36">
            </div>
        </div>
    </div>

    <div class="dn-ft__legal">
        <div class="dn-ft__legal-inner">
            <p class="dn-ft__copy">&copy; {{ date('Y') }} Devbhoomi Naturals. {{ __('All Rights Reserved.') }}</p>
            <p class="dn-ft__from">
                <span class="dn-ft__rule" aria-hidden="true"></span>
                <img src="{{ asset('images/footer-peaks.png') }}?v=2" alt="" width="58" height="37"
                    class="dn-ft__from-img" aria-hidden="true">
                <img src="{{ asset('images/footer-leaf.png') }}?v=1" alt="" width="14" height="15"
                    class="dn-ft__from-leaf" aria-hidden="true">
                <span>{{ __('From the Himalayas') }}</span>
                <img src="{{ asset('images/footer-leaf.png') }}?v=1" alt="" width="12" height="13"
                    class="dn-ft__from-leaf dn-ft__from-leaf--mid" aria-hidden="true">
                <span>{{ __('To Your Home') }}</span>
                <img src="{{ asset('images/footer-leaf.png') }}?v=1" alt="" width="14" height="15"
                    class="dn-ft__from-leaf dn-ft__from-leaf--right" aria-hidden="true">
                <span class="dn-ft__rule" aria-hidden="true"></span>
            </p>
            <p class="dn-ft__credit">
                {{ __('Developed by') }}
                <a href="https://quezent.com/" target="_blank" rel="noopener noreferrer">Quezent Technologies</a>
            </p>
        </div>
    </div>
</footer>