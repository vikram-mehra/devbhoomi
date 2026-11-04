@extends('layouts.admin')

@section('title', !empty($isAdmin) ? __('Platform settings') : __('Homepage FAQs'))

@push('styles')
<style>
    .admin-settings-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Left Sidebar Tabs */
    #settingsTabs,
    .admin-settings-nav {
        background: #ffffff;
        border-radius: 16px;
        padding: 10px;
        border: 1px solid #eef2f6;
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.03);
        position: -webkit-sticky;
        position: sticky;
        top: calc(var(--admin-topbar-h, 64px) + 1.25rem);
        z-index: 100;
        max-height: calc(100vh - var(--admin-topbar-h, 64px) - 2rem);
        overflow-y: auto;
    }

    @media (max-width: 767.98px) {
        #settingsTabs,
        .admin-settings-nav {
            position: static;
            top: auto;
            max-height: none;
            overflow-y: visible;
        }
    }

    .admin-settings-nav .nav-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        padding: 12px 18px;
        font-size: 0.925rem;
        font-weight: 600;
        color: #475569;
        border-radius: 10px;
        border: none;
        background: transparent;
        text-align: left;
        transition: all 0.2s ease;
        margin-bottom: 6px;
    }

    .admin-settings-nav .nav-link:last-child {
        margin-bottom: 0;
    }

    .admin-settings-nav .nav-link:hover {
        background: rgba(45, 90, 61, 0.06);
        color: #2d5a3d;
    }

    .admin-settings-nav .nav-link .admin-tab-chevron {
        display: none;
        font-size: 0.9rem;
        font-weight: 700;
        color: #ffd166;
    }

    .admin-settings-nav .nav-link.active {
        background: linear-gradient(135deg, #2d5a3d 0%, #1e3d2a 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(45, 90, 61, 0.25);
    }

    .admin-settings-nav .nav-link.active .admin-tab-chevron {
        display: inline-block;
        color: #ffd166 !important;
    }

    /* Right Form Content Card */
    .admin-settings-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 2.25rem 2.5rem;
        border: 1px solid #eef2f6;
        box-shadow: 0 2px 16px rgba(15, 23, 42, 0.04);
        min-height: 520px;
    }

    @media (max-width: 767.98px) {
        .admin-settings-card {
            padding: 1.5rem;
            border-radius: 16px;
        }
    }

    .admin-settings-card__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 0.35rem;
        gap: 1rem;
    }

    .admin-settings-card__title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e3d2a;
        margin: 0;
    }

    .admin-settings-card__badge {
        background: rgba(45, 90, 61, 0.08);
        color: #2d5a3d;
        border: 1px solid rgba(45, 90, 61, 0.16);
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        padding: 5px 11px;
        border-radius: 6px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .admin-settings-card__subtitle {
        font-size: 0.885rem;
        color: #94a3b8;
        margin-bottom: 2.25rem;
    }

    .admin-settings-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #2d5a3d;
        margin-bottom: 0.45rem;
    }

    .admin-settings-input {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 15px;
        font-size: 0.925rem;
        color: #0f172a;
        background: #ffffff;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .admin-settings-input:focus {
        border-color: #2d5a3d;
        box-shadow: 0 0 0 3px rgba(45, 90, 61, 0.14);
        outline: none;
    }

    .admin-settings-save-btn {
        background: linear-gradient(135deg, #2d5a3d 0%, #1e3d2a 100%);
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 12px 28px;
        font-weight: 700;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 14px rgba(45, 90, 61, 0.28);
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .admin-settings-save-btn:hover {
        background: linear-gradient(135deg, #1e3d2a 0%, #162f20 100%);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(45, 90, 61, 0.38);
    }

    .admin-settings-outline-btn {
        border: 1.5px solid #2d5a3d;
        color: #2d5a3d;
        background: transparent;
        font-weight: 600;
        border-radius: 8px;
        padding: 6px 14px;
        font-size: 0.825rem;
        text-decoration: none;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
    }

    .admin-settings-outline-btn:hover {
        background: #2d5a3d;
        color: #ffffff;
    }

    /* FAQ Builder Specific Styling */
    .faq-item {
        border-radius: 12px;
        overflow: hidden;
        border-color: #e2e8f0;
        background: #ffffff;
        transition: all 0.2s ease;
    }

    .faq-item:hover {
        border-color: #cbd5e1;
    }

    .faq-item__header {
        background: #f8fafc;
        cursor: pointer;
        user-select: none;
        transition: background 0.15s ease;
    }

    .faq-item__header:hover {
        background: #f1f5f9;
    }

    .faq-order-badge {
        background: rgba(45, 90, 61, 0.1);
        color: #2d5a3d;
        font-weight: 700;
        font-size: 0.785rem;
        padding: 4px 8px;
        border-radius: 6px;
    }

    .faq-btn-icon {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border-radius: 6px;
    }
</style>
@endpush

@section('content')
<div class="admin-settings-container">
    <form method="post" action="{{ route('admin.settings.update') }}" id="adminSettingsForm">
        @csrf
        <input type="hidden" name="active_tab" id="activeTabInput" value="{{ $activeTab ?? (empty($isAdmin) ? 'faqs' : 'platform') }}">

        <div class="row g-4">
            {{-- Left Side: Tab Navigation --}}
            <div class="col-lg-3 col-md-4">
                <div class="admin-settings-nav nav flex-column nav-pills" id="settingsTabs" role="tablist" aria-orientation="vertical">
                    @if(!empty($isAdmin))
                    {{-- Tab 1: Platform Settings --}}
                    <button class="nav-link {{ ($activeTab ?? 'platform') === 'platform' ? 'active' : '' }}" id="tab-platform-btn" data-bs-toggle="pill" data-bs-target="#tab-platform" type="button" role="tab" aria-controls="tab-platform" aria-selected="{{ ($activeTab ?? 'platform') === 'platform' ? 'true' : 'false' }}" data-tab-name="platform">
                        <span>{{ __('Platform Settings') }}</span>
                        <i class="bi bi-chevron-right admin-tab-chevron" aria-hidden="true"></i>
                    </button>

                    {{-- Tab 2: Company & Billing Rules --}}
                    <button class="nav-link {{ ($activeTab ?? '') === 'billing' ? 'active' : '' }}" id="tab-billing-btn" data-bs-toggle="pill" data-bs-target="#tab-billing" type="button" role="tab" aria-controls="tab-billing" aria-selected="{{ ($activeTab ?? '') === 'billing' ? 'true' : 'false' }}" data-tab-name="billing">
                        <span>{{ __('Company & Billing') }}</span>
                        <i class="bi bi-chevron-right admin-tab-chevron" aria-hidden="true"></i>
                    </button>

                    {{-- Tab 3: Footer & Contact Details --}}
                    <button class="nav-link {{ ($activeTab ?? '') === 'contact' ? 'active' : '' }}" id="tab-contact-btn" data-bs-toggle="pill" data-bs-target="#tab-contact" type="button" role="tab" aria-controls="tab-contact" aria-selected="{{ ($activeTab ?? '') === 'contact' ? 'true' : 'false' }}" data-tab-name="contact">
                        <span>{{ __('Footer & Contact') }}</span>
                        <i class="bi bi-chevron-right admin-tab-chevron" aria-hidden="true"></i>
                    </button>

                    {{-- Tab 4: Social Media Links --}}
                    <button class="nav-link {{ ($activeTab ?? '') === 'social' ? 'active' : '' }}" id="tab-social-btn" data-bs-toggle="pill" data-bs-target="#tab-social" type="button" role="tab" aria-controls="tab-social" aria-selected="{{ ($activeTab ?? '') === 'social' ? 'true' : 'false' }}" data-tab-name="social">
                        <span>{{ __('Social Media Links') }}</span>
                        <i class="bi bi-chevron-right admin-tab-chevron" aria-hidden="true"></i>
                    </button>
                    @endif

                    {{-- Tab 5: Homepage FAQs --}}
                    <button class="nav-link {{ (empty($isAdmin) || ($activeTab ?? '') === 'faqs') ? 'active' : '' }}" id="tab-faqs-btn" data-bs-toggle="pill" data-bs-target="#tab-faqs" type="button" role="tab" aria-controls="tab-faqs" aria-selected="{{ (empty($isAdmin) || ($activeTab ?? '') === 'faqs') ? 'true' : 'false' }}" data-tab-name="faqs">
                        <span>{{ __('Homepage FAQs') }}</span>
                        <i class="bi bi-chevron-right admin-tab-chevron" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            {{-- Right Side: Tab Form Panes --}}
            <div class="col-lg-9 col-md-8">
                <div class="admin-settings-card">
                    <div class="tab-content" id="settingsTabContent">
                        
                        @if(!empty($isAdmin))
                        {{-- PANE 1: Platform Settings --}}
                        <div class="tab-pane fade {{ ($activeTab ?? 'platform') === 'platform' ? 'show active' : '' }}" id="tab-platform" role="tabpanel" aria-labelledby="tab-platform-btn">
                            <div class="admin-settings-card__header">
                                <h2 class="admin-settings-card__title">{{ __('Platform Settings') }}</h2>
                                <span class="admin-settings-card__badge">{{ __('Group: Platform') }}</span>
                            </div>
                            <p class="admin-settings-card__subtitle">{{ __('Changes are saved independently for this configuration group.') }}</p>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="admin-settings-label">{{ __('Default Commission %') }}</label>
                                    <input type="number" step="0.1" name="default_commission_percent" class="form-control admin-settings-input" value="{{ $default_commission_percent }}" required>
                                    <div class="form-text small text-muted mt-1">{{ __('Default platform commission percentage applied across vendor sales.') }}</div>
                                </div>
                                <div class="col-md-12">
                                    <div class="p-3 rounded-3 bg-light border">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                            <div>
                                                <strong class="d-block text-dark">{{ __('Shipping & Delivery Charges') }}</strong>
                                                <small class="text-muted">{{ __('Flat rates, free shipping thresholds, and courier settings are configured on dedicated page.') }}</small>
                                            </div>
                                            <a href="{{ route('admin.shipping-settings.edit') }}" class="admin-settings-outline-btn">
                                                <i class="bi bi-truck me-1"></i> {{ __('Manage Shipping Settings') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top">
                                <button type="submit" class="admin-settings-save-btn">
                                    <i class="bi bi-floppy-fill" aria-hidden="true"></i>
                                    <span>{{ __('Save Platform Settings') }}</span>
                                </button>
                            </div>
                        </div>

                        {{-- PANE 2: Company & Billing Rules --}}
                        <div class="tab-pane fade {{ ($activeTab ?? '') === 'billing' ? 'show active' : '' }}" id="tab-billing" role="tabpanel" aria-labelledby="tab-billing-btn">
                            <div class="admin-settings-card__header">
                                <h2 class="admin-settings-card__title">{{ __('Company & Billing Rules') }}</h2>
                                <span class="admin-settings-card__badge">{{ __('Group: Billing') }}</span>
                            </div>
                            <p class="admin-settings-card__subtitle">{{ __('Used on customer invoices, packaging slips, tax filings, and shipping labels.') }}</p>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="admin-settings-label">{{ __('Company / Website Name') }}</label>
                                    <input type="text" name="company_name" class="form-control admin-settings-input" value="{{ $company_name }}" placeholder="e.g. Devbhoomi Naturals">
                                </div>
                                <div class="col-md-6">
                                    <label class="admin-settings-label">{{ __('GSTIN / Tax ID') }}</label>
                                    <input type="text" name="company_gst" class="form-control admin-settings-input" value="{{ $company_gst }}" placeholder="e.g. 05AAZFD6097K1ZK">
                                </div>
                                <div class="col-md-12">
                                    <label class="admin-settings-label">{{ __('Site Logo URL') }}</label>
                                    <input type="url" name="site_logo_url" class="form-control admin-settings-input" value="{{ $site_logo_url }}" placeholder="https://domain.com/images/logo.png">
                                </div>
                                <div class="col-md-6">
                                    <label class="admin-settings-label">{{ __('Company Phone') }}</label>
                                    <input type="text" name="company_phone" class="form-control admin-settings-input" value="{{ $company_phone }}" placeholder="e.g. +91 92177 32670">
                                </div>
                                <div class="col-md-6">
                                    <label class="admin-settings-label">{{ __('Company Billing Email') }}</label>
                                    <input type="email" name="company_email" class="form-control admin-settings-input" value="{{ $company_email }}" placeholder="e.g. support@devbhoominaturals.com">
                                </div>
                                <div class="col-md-12">
                                    <label class="admin-settings-label">{{ __('Company Billing Address') }}</label>
                                    <textarea name="company_address" class="form-control admin-settings-input" rows="3" placeholder="Full street address, city, state, pincode">{{ $company_address }}</textarea>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top">
                                <button type="submit" class="admin-settings-save-btn">
                                    <i class="bi bi-floppy-fill" aria-hidden="true"></i>
                                    <span>{{ __('Save Billing Rules') }}</span>
                                </button>
                            </div>
                        </div>

                        {{-- PANE 3: Footer & Contact Details --}}
                        <div class="tab-pane fade {{ ($activeTab ?? '') === 'contact' ? 'show active' : '' }}" id="tab-contact" role="tabpanel" aria-labelledby="tab-contact-btn">
                            <div class="admin-settings-card__header">
                                <h2 class="admin-settings-card__title">{{ __('Footer & Contact Details') }}</h2>
                                <span class="admin-settings-card__badge">{{ __('Group: Contact') }}</span>
                            </div>
                            <p class="admin-settings-card__subtitle">{{ __('Displayed in website footer & announcement bar. Leave any field blank to hide it.') }}</p>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="admin-settings-label">{{ __('Section Heading') }}</label>
                                    <input type="text" name="footer_contact_heading" class="form-control admin-settings-input" value="{{ $footer_contact_heading }}" placeholder="{{ __('Get in Touch') }}">
                                    <div class="form-text small text-muted mt-1">{{ __('Defaults to "Get in Touch" if blank.') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="admin-settings-label">{{ __('Contact Email') }}</label>
                                    <input type="email" name="contact_email" class="form-control admin-settings-input" value="{{ $contact_email }}" placeholder="support@devbhoominaturals.com">
                                    <div class="form-text small text-muted mt-1">{{ __('Leave blank to hide email from footer.') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="admin-settings-label">{{ __('Contact Phone') }}</label>
                                    <input type="text" name="contact_phone" class="form-control admin-settings-input" value="{{ $contact_phone }}" placeholder="+91 92177 32670">
                                    <div class="form-text small text-muted mt-1">{{ __('Leave blank to hide phone from footer.') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="admin-settings-label">{{ __('WhatsApp Number / Link') }}</label>
                                    <input type="text" name="contact_whatsapp" class="form-control admin-settings-input" value="{{ $contact_whatsapp }}" placeholder="+91 92177 32670">
                                    <div class="form-text small text-muted mt-1">{{ __('Enter phone number or full wa.me link. Leave blank to hide.') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="admin-settings-label">{{ __('Address / Location') }}</label>
                                    <input type="text" name="contact_address" class="form-control admin-settings-input" value="{{ $contact_address }}" placeholder="{{ __('Uttarakhand, India') }}">
                                    <div class="form-text small text-muted mt-1">{{ __('Leave blank to hide address from footer.') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="admin-settings-label">{{ __('Working Hours') }}</label>
                                    <input type="text" name="contact_hours" class="form-control admin-settings-input" value="{{ $contact_hours }}" placeholder="Mon – Sat: 10:00 AM – 6:00 PM">
                                    <div class="form-text small text-muted mt-1">{{ __('Leave blank to hide working hours from footer.') }}</div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top">
                                <button type="submit" class="admin-settings-save-btn">
                                    <i class="bi bi-floppy-fill" aria-hidden="true"></i>
                                    <span>{{ __('Save Contact Details') }}</span>
                                </button>
                            </div>
                        </div>

                        {{-- PANE 4: Social Media Links --}}
                        <div class="tab-pane fade {{ ($activeTab ?? '') === 'social' ? 'show active' : '' }}" id="tab-social" role="tabpanel" aria-labelledby="tab-social-btn">
                            <div class="admin-settings-card__header">
                                <h2 class="admin-settings-card__title">{{ __('Social Media Links') }}</h2>
                                <span class="admin-settings-card__badge">{{ __('Group: Social') }}</span>
                            </div>
                            <p class="admin-settings-card__subtitle">{{ __('Leave any social profile blank to hide its icon in the footer. If all are blank, "Follow Us" is hidden.') }}</p>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="admin-settings-label">
                                        <i class="bi bi-facebook text-primary me-1"></i> {{ __('Facebook URL') }}
                                    </label>
                                    <input type="text" name="social_facebook" class="form-control admin-settings-input" value="{{ $social_facebook }}" placeholder="https://www.facebook.com/your-page">
                                </div>
                                <div class="col-md-6">
                                    <label class="admin-settings-label">
                                        <i class="bi bi-instagram text-danger me-1"></i> {{ __('Instagram URL') }}
                                    </label>
                                    <input type="text" name="social_instagram" class="form-control admin-settings-input" value="{{ $social_instagram }}" placeholder="https://www.instagram.com/your-profile">
                                </div>
                                <div class="col-md-6">
                                    <label class="admin-settings-label">
                                        <i class="bi bi-youtube text-danger me-1"></i> {{ __('YouTube URL') }}
                                    </label>
                                    <input type="text" name="social_youtube" class="form-control admin-settings-input" value="{{ $social_youtube }}" placeholder="https://www.youtube.com/@your-channel">
                                </div>
                                <div class="col-md-6">
                                    <label class="admin-settings-label">
                                        <i class="bi bi-twitter-x text-dark me-1"></i> {{ __('Twitter / X URL') }}
                                    </label>
                                    <input type="text" name="social_twitter" class="form-control admin-settings-input" value="{{ $social_twitter }}" placeholder="https://x.com/your-handle">
                                </div>
                                <div class="col-md-12">
                                    <label class="admin-settings-label">
                                        <i class="bi bi-linkedin text-primary me-1"></i> {{ __('LinkedIn URL') }}
                                    </label>
                                    <input type="text" name="social_linkedin" class="form-control admin-settings-input" value="{{ $social_linkedin }}" placeholder="https://www.linkedin.com/company/your-company">
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top">
                                <button type="submit" class="admin-settings-save-btn">
                                    <i class="bi bi-floppy-fill" aria-hidden="true"></i>
                                    <span>{{ __('Save Social Links') }}</span>
                                </button>
                            </div>
                        </div>
                        @endif

                        {{-- PANE 5: Homepage FAQs --}}
                        <div class="tab-pane fade {{ (empty($isAdmin) || ($activeTab ?? '') === 'faqs') ? 'show active' : '' }}" id="tab-faqs" role="tabpanel" aria-labelledby="tab-faqs-btn">
                            <div class="admin-settings-card__header">
                                <h2 class="admin-settings-card__title">{{ __('Homepage FAQs') }}</h2>
                                <span class="admin-settings-card__badge">{{ __('Group: FAQs') }}</span>
                            </div>
                            <p class="admin-settings-card__subtitle">{{ __('Manage, edit, reorder, and add questions & answers for the homepage accordion and Google rich snippet.') }}</p>

                            {{-- FAQs Toolbar --}}
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-light text-dark border px-2 py-1" id="faqCountBadge">{{ count($faqs) }} {{ __('FAQs') }}</span>
                                    <button type="button" class="btn btn-sm btn-link text-decoration-none text-muted p-0" id="faqToggleAllBtn">
                                        <i class="bi bi-arrows-expand me-1"></i> <span id="faqToggleAllText">{{ __('Expand All') }}</span>
                                    </button>
                                </div>
                                <button type="button" class="btn btn-sm btn-success fw-semibold d-inline-flex align-items-center gap-1 shadow-sm" id="addFaqBtn">
                                    <i class="bi bi-plus-circle-fill"></i> {{ __('Add New FAQ') }}
                                </button>
                            </div>

                            {{-- FAQ Items Container --}}
                            <div id="faqList" class="d-flex flex-column gap-3 mb-4">
                                @forelse($faqs as $idx => $faq)
                                    <div class="faq-item card shadow-none" data-faq-index="{{ $idx }}">
                                        <div class="card-header faq-item__header d-flex align-items-center justify-content-between py-2 px-3">
                                            <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                                                <span class="faq-order-badge">#{{ $idx + 1 }}</span>
                                                <strong class="text-truncate faq-preview-title" style="max-width: 420px; font-size: 0.885rem; color: #1e293b;">{{ $faq['question'] }}</strong>
                                            </div>
                                            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                                <button type="button" class="btn btn-sm btn-light border faq-btn-icon faq-move-up-btn" title="{{ __('Move Up') }}">
                                                    <i class="bi bi-arrow-up"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-light border faq-btn-icon faq-move-down-btn" title="{{ __('Move Down') }}">
                                                    <i class="bi bi-arrow-down"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary px-2 py-1 faq-edit-toggle-btn" title="{{ __('Edit') }}">
                                                    <i class="bi bi-pencil-square me-1"></i> <span class="faq-edit-btn-text">{{ __('Edit') }}</span>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger faq-btn-icon faq-delete-btn" title="{{ __('Delete') }}">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body faq-body p-3 d-none">
                                            <div class="mb-3">
                                                <label class="admin-settings-label">{{ __('Question') }}</label>
                                                <input type="text" name="faqs[{{ $idx }}][question]" class="form-control admin-settings-input faq-question-input" value="{{ $faq['question'] }}" placeholder="e.g. Do you deliver across India?" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="admin-settings-label">{{ __('Answer') }}</label>
                                                <textarea name="faqs[{{ $idx }}][answer]" class="form-control admin-settings-input faq-answer-input" rows="3" placeholder="e.g. Yes, we deliver pan-India via express courier partners." required>{{ $faq['answer'] }}</textarea>
                                            </div>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" name="faqs[{{ $idx }}][track_link]" value="1" id="track_link_{{ $idx }}" {{ !empty($faq['track_link']) ? 'checked' : '' }}>
                                                <label class="form-check-label small text-muted" for="track_link_{{ $idx }}">
                                                    {{ __('Include quick "Track order" link button in answer') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4 text-muted bg-light rounded-3" id="noFaqsMsg">
                                        <i class="bi bi-question-circle display-6 d-block mb-2 text-secondary"></i>
                                        <p class="mb-2">{{ __('No FAQs added yet.') }}</p>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="document.getElementById('addFaqBtn').click();">
                                            <i class="bi bi-plus-circle me-1"></i> {{ __('Add Your First FAQ') }}
                                        </button>
                                    </div>
                                @endforelse
                            </div>

                            <div class="mt-4 pt-3 border-top">
                                <button type="submit" class="admin-settings-save-btn">
                                    <i class="bi bi-floppy-fill" aria-hidden="true"></i>
                                    <span>{{ __('Save Homepage FAQs') }}</span>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const activeTabInput = document.getElementById('activeTabInput');
        const tabButtons = document.querySelectorAll('#settingsTabs button[data-bs-toggle="pill"]');

        // Function to activate tab by name ('platform', 'billing', 'contact', 'social', 'faqs')
        function activateTab(tabName) {
            if (!tabName) return;
            const targetBtn = document.querySelector(`#settingsTabs button[data-tab-name="${tabName}"]`);
            if (targetBtn && window.bootstrap && window.bootstrap.Tab) {
                const tabInstance = bootstrap.Tab.getOrCreateInstance(targetBtn);
                tabInstance.show();
                if (activeTabInput) activeTabInput.value = tabName;
            }
        }

        // Listen for tab changes and sync activeTabInput and hash
        tabButtons.forEach(btn => {
            btn.addEventListener('shown.bs.tab', function (e) {
                const tabName = e.target.getAttribute('data-tab-name');
                if (activeTabInput && tabName) {
                    activeTabInput.value = tabName;
                    if (history.replaceState) {
                        history.replaceState(null, null, '#' + tabName);
                    } else {
                        window.location.hash = tabName;
                    }
                }
            });
        });

        // Check if there is a hash or session tab
        let initialTab = '';
        if (window.location.hash) {
            initialTab = window.location.hash.replace('#', '');
        } else if (activeTabInput && activeTabInput.value) {
            initialTab = activeTabInput.value;
        }

        if (initialTab) {
            activateTab(initialTab);
        }

        // --- FAQ Builder JavaScript ---
        const faqList = document.getElementById('faqList');
        const addFaqBtn = document.getElementById('addFaqBtn');
        const faqCountBadge = document.getElementById('faqCountBadge');
        const faqToggleAllBtn = document.getElementById('faqToggleAllBtn');
        const faqToggleAllText = document.getElementById('faqToggleAllText');

        function updateFaqIndexes() {
            const items = faqList.querySelectorAll('.faq-item');
            if (faqCountBadge) {
                faqCountBadge.textContent = items.length + ' FAQs';
            }

            items.forEach((item, index) => {
                item.setAttribute('data-faq-index', index);

                // Update order badge
                const badge = item.querySelector('.faq-order-badge');
                if (badge) badge.textContent = '#' + (index + 1);

                // Update input names
                const qInput = item.querySelector('.faq-question-input');
                if (qInput) qInput.setAttribute('name', `faqs[${index}][question]`);

                const aInput = item.querySelector('.faq-answer-input');
                if (aInput) aInput.setAttribute('name', `faqs[${index}][answer]`);

                const tInput = item.querySelector('.form-check-input');
                if (tInput) {
                    tInput.setAttribute('name', `faqs[${index}][track_link]`);
                    tInput.setAttribute('id', `track_link_${index}`);
                    const tLabel = item.querySelector('.form-check-label');
                    if (tLabel) tLabel.setAttribute('for', `track_link_${index}`);
                }

                // Update Move button states
                const upBtn = item.querySelector('.faq-move-up-btn');
                const downBtn = item.querySelector('.faq-move-down-btn');
                if (upBtn) upBtn.disabled = (index === 0);
                if (downBtn) downBtn.disabled = (index === items.length - 1);
            });
        }

        // Initialize button states
        updateFaqIndexes();

        // FAQ List Click Handlers (Move Up, Move Down, Edit Toggle, Delete)
        if (faqList) {
            faqList.addEventListener('click', function (e) {
                const item = e.target.closest('.faq-item');
                if (!item) return;

                // Move Up
                if (e.target.closest('.faq-move-up-btn')) {
                    e.preventDefault();
                    const prev = item.previousElementSibling;
                    if (prev && prev.classList.contains('faq-item')) {
                        faqList.insertBefore(item, prev);
                        updateFaqIndexes();
                    }
                    return;
                }

                // Move Down
                if (e.target.closest('.faq-move-down-btn')) {
                    e.preventDefault();
                    const next = item.nextElementSibling;
                    if (next && next.classList.contains('faq-item')) {
                        faqList.insertBefore(next, item);
                        updateFaqIndexes();
                    }
                    return;
                }

                // Edit Toggle Button or Header Click
                if (e.target.closest('.faq-edit-toggle-btn') || (e.target.closest('.faq-item__header') && !e.target.closest('button'))) {
                    e.preventDefault();
                    const body = item.querySelector('.faq-body');
                    const btnText = item.querySelector('.faq-edit-btn-text');
                    const editBtn = item.querySelector('.faq-edit-toggle-btn');
                    if (body) {
                        body.classList.toggle('d-none');
                        const isHidden = body.classList.contains('d-none');
                        if (btnText) btnText.textContent = isHidden ? 'Edit' : 'Close';
                        if (editBtn) {
                            if (isHidden) {
                                editBtn.classList.remove('btn-primary');
                                editBtn.classList.add('btn-outline-primary');
                            } else {
                                editBtn.classList.remove('btn-outline-primary');
                                editBtn.classList.add('btn-primary');
                            }
                        }
                    }
                    return;
                }

                // Delete Button
                if (e.target.closest('.faq-delete-btn')) {
                    e.preventDefault();
                    const q = item.querySelector('.faq-question-input')?.value.trim();
                    if (q && !confirm(`Delete FAQ: "${q}"?`)) {
                        return;
                    }
                    item.remove();
                    updateFaqIndexes();
                    return;
                }
            });

            // Sync Question Input with Header Preview Title in real time
            faqList.addEventListener('input', function (e) {
                if (e.target.classList.contains('faq-question-input')) {
                    const item = e.target.closest('.faq-item');
                    const titlePreview = item?.querySelector('.faq-preview-title');
                    if (titlePreview) {
                        titlePreview.textContent = e.target.value.trim() || 'New Question';
                    }
                }
            });
        }

        // Add FAQ Button
        if (addFaqBtn) {
            addFaqBtn.addEventListener('click', function () {
                const noMsg = document.getElementById('noFaqsMsg');
                if (noMsg) noMsg.remove();

                const currentCount = faqList.querySelectorAll('.faq-item').length;
                const newCard = document.createElement('div');
                newCard.className = 'faq-item card shadow-none';
                newCard.setAttribute('data-faq-index', currentCount);
                newCard.innerHTML = `
                    <div class="card-header faq-item__header d-flex align-items-center justify-content-between py-2 px-3">
                        <div class="d-flex align-items-center gap-2 overflow-hidden me-2">
                            <span class="faq-order-badge">#${currentCount + 1}</span>
                            <strong class="text-truncate faq-preview-title" style="max-width: 420px; font-size: 0.885rem; color: #1e293b;">New Question</strong>
                        </div>
                        <div class="d-flex align-items-center gap-1 flex-shrink-0">
                            <button type="button" class="btn btn-sm btn-light border faq-btn-icon faq-move-up-btn" title="Move Up">
                                <i class="bi bi-arrow-up"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-light border faq-btn-icon faq-move-down-btn" title="Move Down">
                                <i class="bi bi-arrow-down"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-primary px-2 py-1 faq-edit-toggle-btn" title="Edit">
                                <i class="bi bi-pencil-square me-1"></i> <span class="faq-edit-btn-text">Close</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger faq-btn-icon faq-delete-btn" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body faq-body p-3">
                        <div class="mb-3">
                            <label class="admin-settings-label">Question</label>
                            <input type="text" name="faqs[${currentCount}][question]" class="form-control admin-settings-input faq-question-input" value="" placeholder="e.g. Do you deliver across India?" required>
                        </div>
                        <div class="mb-2">
                            <label class="admin-settings-label">Answer</label>
                            <textarea name="faqs[${currentCount}][answer]" class="form-control admin-settings-input faq-answer-input" rows="3" placeholder="e.g. Yes, we deliver pan-India via express courier partners." required></textarea>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="faqs[${currentCount}][track_link]" value="1" id="track_link_${currentCount}">
                            <label class="form-check-label small text-muted" for="track_link_${currentCount}">
                                Include quick "Track order" link button in answer
                            </label>
                        </div>
                    </div>
                `;

                faqList.appendChild(newCard);
                updateFaqIndexes();

                const newQInput = newCard.querySelector('.faq-question-input');
                if (newQInput) {
                    newQInput.focus();
                }
            });
        }

        // Toggle Expand / Collapse All
        if (faqToggleAllBtn) {
            let allCollapsed = true;
            faqToggleAllBtn.addEventListener('click', function () {
                allCollapsed = !allCollapsed;
                const bodies = faqList.querySelectorAll('.faq-body');
                const btnTexts = faqList.querySelectorAll('.faq-edit-btn-text');
                const editBtns = faqList.querySelectorAll('.faq-edit-toggle-btn');

                bodies.forEach(b => {
                    if (allCollapsed) {
                        b.classList.add('d-none');
                    } else {
                        b.classList.remove('d-none');
                    }
                });

                btnTexts.forEach(t => {
                    t.textContent = allCollapsed ? 'Edit' : 'Close';
                });

                editBtns.forEach(btn => {
                    if (allCollapsed) {
                        btn.classList.remove('btn-primary');
                        btn.classList.add('btn-outline-primary');
                    } else {
                        btn.classList.remove('btn-outline-primary');
                        btn.classList.add('btn-primary');
                    }
                });

                faqToggleAllText.textContent = allCollapsed ? 'Expand All' : 'Collapse All';
            });
        }
    });
</script>
@endpush
@endsection
