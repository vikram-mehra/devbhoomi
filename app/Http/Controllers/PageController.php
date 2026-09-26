<?php

namespace App\Http\Controllers;

use App\Models\AboutPage;
use App\Models\ContactInquiry;
use App\Models\ContactPage;
use App\Services\ContactCaptcha;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

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

    public function contactCaptcha(ContactCaptcha $captcha): Response
    {
        return $captcha->imageResponse();
    }

    public function contactSubmit(Request $request, ContactCaptcha $captcha)
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
            'captcha' => 'required|string|max:12',
        ]);

        if (! $captcha->matches($data['captcha'] ?? null)) {
            $captcha->forget();

            throw ValidationException::withMessages([
                'captcha' => __('The security code is incorrect. Please try again.'),
            ]);
        }

        $captcha->forget();
        unset($data['captcha']);

        ContactInquiry::create($data);

        return redirect()
            ->route('pages.contact')
            ->with('contact_sent', true);
    }
}
