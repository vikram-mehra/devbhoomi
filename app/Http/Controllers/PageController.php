<?php

namespace App\Http\Controllers;

use App\Models\AboutPage;
use App\Models\ContactInquiry;
use App\Models\ContactPage;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function about()
    {
        $page = AboutPage::cached();
        if (! $page->is_published) {
            abort(404);
        }

        return view('market.pages.about', compact('page'));
    }

    public function contact()
    {
        $page = ContactPage::cached();
        if (! $page->is_published) {
            abort(404);
        }

        return view('market.pages.contact', compact('page'));
    }

    public function contactSubmit(Request $request)
    {
        $page = ContactPage::cached();
        if (! $page->is_published) {
            abort(404);
        }

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'subject' => 'nullable|string|max:200',
            'message' => 'required|string|max:5000',
        ]);

        ContactInquiry::create($data);

        return redirect()
            ->route('pages.contact')
            ->with('contact_sent', true);
    }
}
