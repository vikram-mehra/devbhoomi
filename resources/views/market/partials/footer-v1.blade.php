@php $footerBrand = config('app.name', 'Devbhoomi Naturals'); @endphp
    <footer class="cb-footer pro-footer-mk">
        <div class="cb-container position-relative">
            <div class="row g-4 g-xl-5 pro-footer-mk__main py-2 align-items-start">
                <div class="col-12 col-md-6 col-xl-3">
                    <a href="{{ route('market.home') }}"
                        class="pro-footer-mk__brand mb-3 text-decoration-none d-inline-block">
                        <span class="pro-footer-mk__logo-text font-anc-serif">
                            <span class="pro-footer-mk__logo-main">{{ $footerBrand }}</span>
                        </span>
                    </a>
                    <p class="pro-footer-mk__desc small mb-4">
                        {{ __('Discover the latest trends and enjoy seamless shopping with our exclusive collections.') }}
                    </p>
                    <ul class="list-unstyled pro-footer-mk__contact mb-0">
                        <li class="pro-footer-mk__contact-item">
                            <i class="bi bi-geo-alt" aria-hidden="true"></i>
                            <span>{{ config('app.name') }}, {{ __('Ranikhet, Uttarakhand') }}, {{ __('India') }}</span>
                        </li>
                        <li class="pro-footer-mk__contact-item">
                            <i class="bi bi-telephone" aria-hidden="true"></i>
                            <a href="tel:+919217732670">{{ __('Call Us') }}: +91 9217732670 </a>
                        </li>
                        <li class="pro-footer-mk__contact-item">
                            <i class="bi bi-envelope" aria-hidden="true"></i>
                            <a href="mailto:support@devbhoominaturals.com">{{ __('Email Us') }}:
                                support@devbhoominaturals.com</a>
                        </li>
                    </ul>
                </div>
                <div class="col-6 col-lg-4 col-xl-2">
                    <h3 class="cb-footer-heading pro-footer-mk__heading">{{ __('Our Products') }}</h3>
                    @include('market.partials.menu-footer-links')
                </div>
                <div class="col-6 col-lg-4 col-xl-2">
                    <h3 class="cb-footer-heading pro-footer-mk__heading">{{ __('Useful links') }}</h3>
                    <a href="{{ route('market.home') }}">{{ __('Home') }}</a>
                    <a href="{{ route('pages.about') }}">{{ __('About us') }}</a>
                    <a href="{{ route('blog.index') }}">{{ __('Blogs') }}</a>
                    <a href="{{ route('offers.index') }}">{{ __('Offers') }}</a>
                    <a href="{{ route('shop.search') }}">{{ __('Search') }}</a>
                </div>
                <div class="col-6 col-lg-4 col-xl-2">
                    <h3 class="cb-footer-heading pro-footer-mk__heading">{{ __('Help center') }}</h3>
                    @auth
                        <a href="{{ route('account.dashboard') }}">{{ __('My account') }}</a>
                    @else
                        <a href="{{ route('login') }}">{{ __('My account') }}</a>
                    @endauth
                    @auth
                        <a href="{{ route('orders.track') }}">{{ __('Track order') }}</a>
                    @else
                        <a href="{{ route('login') }}">{{ __('Track order') }}</a>
                    @endauth
                    <a href="{{ route('pages.contact') }}">{{ __('Contact us') }}</a>
                    <a href="{{ route('legal.terms') }}">{{ __('Terms & conditions') }}</a>
                    <a href="{{ route('legal.privacy') }}">{{ __('Privacy policy') }}</a>
                    <a href="{{ route('legal.refund') }}">{{ __('Refund policy') }}</a>
                    <a href="{{ route('legal.shipping') }}">{{ __('Shipping policy') }}</a>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <h3 class="cb-footer-heading pro-footer-mk__heading">{{ __('Follow us') }}</h3>
                    <p class="pro-footer-mk__news-text small mb-3">
                        {{ __('Never miss anything from store by signing up to our newsletter.') }}
                    </p>
                    <form id="cbFooterNewsForm" class="pro-footer-mk__news-form d-flex flex-column gap-2 mb-3">
                        @csrf
                        <input type="email" name="email" required class="form-control pro-footer-mk__news-input"
                            placeholder="{{ __('Enter email address') }}" autocomplete="email"
                            aria-label="{{ __('Email') }}">
                        <button type="submit" class="btn pro-footer-mk__subscribe w-100">{{ __('Subscribe') }}</button>
                    </form>
                    <p id="cbFooterNewsThanks" class="small text-success mb-0" hidden>
                        {{ __('Thanks — you are subscribed.') }}
                    </p>
                    <div class="pro-footer-mk__social d-flex flex-wrap gap-2">
                        <a href="https://www.facebook.com/share/1D7KtFBEGi/?mibextid=wwXIfr"
                            class="pro-footer-mk__social-btn" aria-label="Facebook"><i class="bi bi-facebook"
                                aria-hidden="true"></i></a>
                        <a href="#" class="pro-footer-mk__social-btn" aria-label="Twitter"><i class="bi bi-twitter-x"
                                aria-hidden="true"></i></a>
                        <a href="https://www.instagram.com/dev_bhoominaturals?igsh=MXJwZXYzeDQwamNjMw%3D%3D"
                            class="pro-footer-mk__social-btn" aria-label="Instagram"><i class="bi bi-instagram"
                                aria-hidden="true"></i></a>
                        <a href="https://wa.me/919217732670?text=Hi"
                            class="pro-footer-mk__social-btn pro-footer-mk__social-btn--whatsapp" aria-label="WhatsApp"
                            target="_blank" rel="noopener"><i class="bi bi-whatsapp" aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="pro-footer-mk__bar cb-footer-bottom">
            <div class="cb-container">
                <div class="pro-footer-mk__bar-inner">
                    <p class="pro-footer-mk__copy small mb-0">&copy; {{ date('Y') }}
                        {{ config('app.name') }}. {{ __('All rights reserved.') }}
                    </p>
                    <div class="pro-footer-mk__payments" aria-label="{{ __('Payment methods') }}">
                        <img src="{{ asset('images/payments/visa.svg') }}" alt="Visa" width="48" height="30">
                        <img src="{{ asset('images/payments/mastercard.svg') }}" alt="Mastercard" width="48" height="30">
                        <img src="{{ asset('images/payments/rupay.svg') }}" alt="RuPay" width="52" height="30">
                        <img src="{{ asset('images/payments/upi.svg') }}" alt="UPI" width="42" height="30">
                        <img src="{{ asset('images/payments/razorpay.svg') }}" alt="Razorpay" width="68" height="30">
                    </div>
                    <p class="pro-footer-mk__credit small mb-0">
                        {{ __('Developed by') }}
                        <a href="https://quezent.com/" target="_blank" rel="noopener noreferrer">Quezent Technologies</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>