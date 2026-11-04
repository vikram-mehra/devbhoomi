<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactPage;
use App\Models\Setting;
use App\Services\SeoService;
use Illuminate\Http\Request;

class SettingAdminController extends Controller
{
    public function edit()
    {
        // Load FAQs from DB or fallback to default
        $faqJson = Setting::getValue('seo.faq_schema_json');
        $faqs = [];
        if (filled($faqJson)) {
            $decoded = json_decode($faqJson, true);
            if (is_array($decoded)) {
                $faqs = array_values(array_filter($decoded, fn ($r) => filled($r['question'] ?? null) && filled($r['answer'] ?? null)));
            }
        }
        if (empty($faqs)) {
            $faqs = app(SeoService::class)->homepageFaqItems();
        }

        $settings = [
            'default_commission_percent' => Setting::getValue('default_commission_percent', '12'),
            'company_name' => Setting::getValue('company_name', config('app.name')),
            'site_logo_url' => Setting::getValue('site_logo_url', ''),
            'company_gst' => Setting::getValue('company_gst', 'GSTIN-NA'),
            'company_phone' => Setting::getValue('company_phone', ''),
            'company_email' => Setting::getValue('company_email', ''),
            'company_address' => Setting::getValue('company_address', ''),

            // Footer "Get in Touch" & Contact Information
            'footer_contact_heading' => Setting::getValue('footer_contact_heading', 'Get in Touch'),
            'contact_email' => Setting::getValue('contact_email', 'support@devbhoominaturals.com'),
            'contact_phone' => Setting::getValue('contact_phone', '+91 92177 32670'),
            'contact_address' => Setting::getValue('contact_address', 'Uttarakhand, India'),
            'contact_hours' => Setting::getValue('contact_hours', 'Mon – Sat: 10:00 AM – 6:00 PM'),
            'contact_whatsapp' => Setting::getValue('contact_whatsapp', '+91 92177 32670'),

            // Social Media Links (Footer)
            'social_facebook' => Setting::getValue('social_facebook', ''),
            'social_instagram' => Setting::getValue('social_instagram', ''),
            'social_youtube' => Setting::getValue('social_youtube', ''),
            'social_twitter' => Setting::getValue('social_twitter', ''),
            'social_linkedin' => Setting::getValue('social_linkedin', ''),

            // Homepage FAQs
            'faqs' => $faqs,
            'isAdmin' => auth()->user()->isAdmin(),
            'activeTab' => (! auth()->user()->isAdmin() && auth()->user()->isSeo()) ? 'faqs' : session('active_tab', 'platform'),
        ];

        return view('admin.settings', $settings);
    }

    public function update(Request $request)
    {
        // SEO users have dedicated access to Homepage FAQs (protecting company, billing & platform settings)
        if (! $request->user()->isAdmin() && $request->user()->isSeo()) {
            $request->validate([
                'faqs' => 'nullable|array',
                'faqs.*.question' => 'nullable|string|max:500',
                'faqs.*.answer' => 'nullable|string|max:5000',
            ]);

            if ($request->has('faqs')) {
                $faqs = [];
                foreach ($request->input('faqs', []) as $row) {
                    $q = trim((string) ($row['question'] ?? ''));
                    $a = trim((string) ($row['answer'] ?? ''));
                    if ($q !== '' && $a !== '') {
                        $item = ['question' => $q, 'answer' => $a];
                        if (!empty($row['track_link'])) {
                            $item['track_link'] = true;
                        }
                        $faqs[] = $item;
                    }
                }
                Setting::setValue('seo.faq_schema_json', json_encode($faqs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            }

            return back()->with('status', __('Homepage FAQs saved successfully.'))->with('active_tab', 'faqs');
        }

        $request->validate([
            'default_commission_percent' => 'required|numeric|min:0|max:90',
            'company_name' => 'nullable|string|max:255',
            'site_logo_url' => 'nullable|url|max:500',
            'company_gst' => 'nullable|string|max:50',
            'company_phone' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:100',
            'company_address' => 'nullable|string|max:1000',

            // Footer Contact Details
            'footer_contact_heading' => 'nullable|string|max:120',
            'contact_email' => 'nullable|email|max:100',
            'contact_phone' => 'nullable|string|max:50',
            'contact_address' => 'nullable|string|max:500',
            'contact_hours' => 'nullable|string|max:120',
            'contact_whatsapp' => 'nullable|string|max:100',

            // Social Media Links
            'social_facebook' => 'nullable|string|max:500',
            'social_instagram' => 'nullable|string|max:500',
            'social_youtube' => 'nullable|string|max:500',
            'social_twitter' => 'nullable|string|max:500',
            'social_linkedin' => 'nullable|string|max:500',

            // FAQs
            'faqs' => 'nullable|array',
            'faqs.*.question' => 'nullable|string|max:500',
            'faqs.*.answer' => 'nullable|string|max:5000',
        ]);

        Setting::setValue('default_commission_percent', (string) $request->default_commission_percent);
        Setting::setValue('company_name', $request->company_name);
        Setting::setValue('site_logo_url', $request->site_logo_url);
        Setting::setValue('company_gst', $request->company_gst);
        Setting::setValue('company_phone', $request->company_phone);
        Setting::setValue('company_email', $request->company_email);
        Setting::setValue('company_address', $request->company_address);

        // Footer & Contact Details (store null if blank so footer/contact know not to display)
        Setting::setValue('footer_contact_heading', $request->filled('footer_contact_heading') ? trim($request->footer_contact_heading) : null);
        Setting::setValue('contact_email', $request->filled('contact_email') ? trim($request->contact_email) : null);
        Setting::setValue('contact_phone', $request->filled('contact_phone') ? trim($request->contact_phone) : null);
        Setting::setValue('contact_address', $request->filled('contact_address') ? trim($request->contact_address) : null);
        Setting::setValue('contact_hours', $request->filled('contact_hours') ? trim($request->contact_hours) : null);
        Setting::setValue('contact_whatsapp', $request->filled('contact_whatsapp') ? trim($request->contact_whatsapp) : null);

        // Keep ContactPage synchronized with Admin Settings
        $contactPage = ContactPage::first();
        if ($contactPage) {
            $contactPage->update([
                'email' => $request->filled('contact_email') ? trim($request->contact_email) : null,
                'phone' => $request->filled('contact_phone') ? trim($request->contact_phone) : null,
                'whatsapp' => $request->filled('contact_whatsapp') ? trim($request->contact_whatsapp) : null,
                'address' => $request->filled('contact_address') ? trim($request->contact_address) : null,
                'hours_weekdays' => $request->filled('contact_hours') ? trim($request->contact_hours) : null,
            ]);
            ContactPage::flushCache();
        }

        // Social Media Links (store null if blank or '#' so footer hides them)
        Setting::setValue('social_facebook', $this->normalizeUrl($request->social_facebook));
        Setting::setValue('social_instagram', $this->normalizeUrl($request->social_instagram));
        Setting::setValue('social_youtube', $this->normalizeUrl($request->social_youtube));
        Setting::setValue('social_twitter', $this->normalizeUrl($request->social_twitter));
        Setting::setValue('social_linkedin', $this->normalizeUrl($request->social_linkedin));

        // Homepage FAQs
        if ($request->has('faqs')) {
            $faqs = [];
            foreach ($request->input('faqs', []) as $row) {
                $q = trim((string) ($row['question'] ?? ''));
                $a = trim((string) ($row['answer'] ?? ''));
                if ($q !== '' && $a !== '') {
                    $item = ['question' => $q, 'answer' => $a];
                    if (!empty($row['track_link'])) {
                        $item['track_link'] = true;
                    }
                    $faqs[] = $item;
                }
            }
            Setting::setValue('seo.faq_schema_json', json_encode($faqs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        return back()->with('status', 'Settings saved.')->with('active_tab', $request->input('active_tab', 'platform'));
    }

    protected function normalizeUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '' || $url === '#') {
            return null;
        }

        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        return $url;
    }
}
