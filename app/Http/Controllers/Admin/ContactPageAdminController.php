<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use App\Models\ContactPage;
use Illuminate\Http\Request;

class ContactPageAdminController extends Controller
{
    public function edit()
    {
        $page = ContactPage::first() ?: ContactPage::createDefault();
        $inquiries = ContactInquiry::orderByDesc('id')->limit(20)->get();

        return view('admin.pages.contact', compact('page', 'inquiries'));
    }

    public function update(Request $request)
    {
        $page = ContactPage::first() ?: ContactPage::createDefault();

        $data = $request->validate([
            'hero_title' => 'required|string|max:255',
            'hero_subtitle' => 'nullable|string|max:2000',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:40',
            'whatsapp' => 'nullable|string|max:40',
            'address' => 'nullable|string|max:2000',
            'map_url' => 'nullable|string|max:2048',
            'hours_weekdays' => 'nullable|string|max:120',
            'hours_weekend' => 'nullable|string|max:120',
            'form_heading' => 'nullable|string|max:255',
            'form_subtext' => 'nullable|string|max:2000',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'canonical_url' => 'nullable|string|max:2048',
            'og_image' => 'nullable|string|max:2048',
            'is_published' => 'nullable|boolean',
        ]);

        $data['is_published'] = $request->boolean('is_published');
        $page->update($data);
        ContactPage::flushCache();

        return back()->with('status', __('Contact page saved.'));
    }

    public function markInquiryRead(ContactInquiry $contactInquiry)
    {
        $contactInquiry->update(['read_at' => now()]);

        return back()->with('status', __('Message marked as read.'));
    }
}
