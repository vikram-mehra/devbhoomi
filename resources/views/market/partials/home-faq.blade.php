@php
    $faqJson = app(\App\Services\SeoService::class)->global('faq_schema_json');
    $faqItems = [];
    if (filled($faqJson)) {
        $decoded = json_decode($faqJson, true);
        if (is_array($decoded)) {
            $faqItems = array_values(array_filter($decoded, fn ($row) => filled($row['question'] ?? null) && filled($row['answer'] ?? null)));
        }
    }
    if (empty($faqItems)) {
        $faqItems = [
            ['question' => __('Are Devbhoomi products 100% organic?'), 'answer' => __('We source pure Himalayan organic millets, pulses and spices directly from verified Uttarakhand farmers.')],
            ['question' => __('Do you deliver across India?'), 'answer' => __('Yes — we ship pan-India. Free delivery on prepaid orders above ₹499.')],
            ['question' => __('How can I track my order?'), 'answer' => __('Sign in to My Orders after checkout to view status and tracking details.')],
        ];
    }
@endphp
@if(!empty($faqItems))
<section class="mk-section cb-reveal" aria-labelledby="faq-heading">
    <div class="pro-section-head">
        <p class="pro-section-head__eyebrow">{{ __('Help center') }}</p>
        <h2 class="pro-section-head__title" id="faq-heading">{{ __('Frequently asked questions') }}</h2>
    </div>
    <div class="accordion pro-faq-accordion" id="homeFaq">
        @foreach($faqItems as $i => $faq)
            <div class="accordion-item border-0 mb-2 shadow-sm rounded-3 overflow-hidden">
                <h3 class="accordion-header" id="faq-h-{{ $i }}">
                    <button class="accordion-button {{ $i > 0 ? 'collapsed' : '' }} fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq-c-{{ $i }}" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}" aria-controls="faq-c-{{ $i }}">
                        {{ $faq['question'] }}
                    </button>
                </h3>
                <div id="faq-c-{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" aria-labelledby="faq-h-{{ $i }}" data-bs-parent="#homeFaq">
                    <div class="accordion-body text-secondary">
                        {{ $faq['answer'] }}
                        @if($i === 2)
                            <a href="{{ route('pages.contact') }}" class="d-inline-block mt-2">{{ __('Contact us') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>
@endif
