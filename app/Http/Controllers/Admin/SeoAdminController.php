<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SeoAuditService;
use App\Services\SeoService;
use App\Models\Setting;
use Illuminate\Http\Request;

class SeoAdminController extends Controller
{
    /** @var SeoService */
    private $seo;

    /** @var SeoAuditService */
    private $audit;

    public function __construct(SeoService $seo, SeoAuditService $audit)
    {
        $this->seo = $seo;
        $this->audit = $audit;
    }

    public function index()
    {
        $settings = [
            'site_title_suffix' => $this->seo->global('site_title_suffix'),
            'default_description' => $this->seo->global('default_description'),
            'default_keywords' => $this->seo->global('default_keywords'),
            'default_og_image' => $this->seo->global('default_og_image'),
            'google_analytics_id' => $this->seo->global('google_analytics_id'),
            'twitter_handle' => $this->seo->global('twitter_handle'),
            'faq_schema_json' => $this->seo->global('faq_schema_json'),
        ];

        $preview = $this->seo->build([
            'title' => 'Pure Himalayan Organic Products',
            'description' => $settings['default_description'],
            'keywords' => $settings['default_keywords'],
            'og_image' => $settings['default_og_image'],
        ]);

        $score = $this->seo->scorePage([
            'title_length' => mb_strlen($preview->title) >= 50 && mb_strlen($preview->title) <= 60,
            'description_length' => mb_strlen($preview->description) >= 150,
            'has_h1' => true,
            'has_canonical' => true,
            'has_og_image' => filled($preview->ogImage),
            'has_schema' => true,
            'images_have_alt' => true,
            'internal_links' => true,
            'mobile_friendly' => true,
        ]);

        return view('admin.seo.index', compact('settings', 'preview', 'score'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_title_suffix' => 'required|string|max:120',
            'default_description' => 'required|string|max:500',
            'default_keywords' => 'nullable|string|max:500',
            'default_og_image' => 'nullable|string|max:2048',
            'google_analytics_id' => 'nullable|string|max:32',
            'twitter_handle' => 'nullable|string|max:64',
            'faq_schema_json' => 'nullable|string|max:65000',
        ]);

        foreach ($validated as $key => $value) {
            Setting::setValue('seo.'.$key, $value);
        }

        return back()->with('status', __('SEO settings saved.'));
    }

    public function report()
    {
        $issues = $this->audit->run();
        $counts = [
            'high' => count(array_filter($issues, fn ($i) => $i['priority'] === 'high')),
            'medium' => count(array_filter($issues, fn ($i) => $i['priority'] === 'medium')),
            'low' => count(array_filter($issues, fn ($i) => $i['priority'] === 'low')),
        ];

        return view('admin.seo.report', compact('issues', 'counts'));
    }

    public function applyFixes()
    {
        $fixed = $this->audit->applyAutoFixes();

        return redirect()
            ->route('admin.seo.report')
            ->with('status', count($fixed).' '.__('auto-fixes applied.'))
            ->with('fixes', $fixed);
    }
}
