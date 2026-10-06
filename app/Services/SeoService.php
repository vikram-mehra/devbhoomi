<?php

namespace App\Services;

use App\Models\Setting;
use App\Support\SeoMeta;
use Illuminate\Support\Str;

class SeoService
{
    public function global(string $key, $default = null)
    {
        $map = [
            'default_title' => config('seo.default_title'),
            'site_title_suffix' => config('seo.default_title_suffix'),
            'default_description' => config('seo.default_description'),
            'default_keywords' => config('seo.default_keywords'),
            'default_og_image' => config('seo.default_og_image'),
            'google_analytics_id' => config('services.google.analytics_id') ?: 'G-XLTR42JC0T',
            'twitter_handle' => '',
            'facebook_app_id' => '',
            'faq_schema_json' => '',
        ];

        $fallback = $map[$key] ?? $default;

        return Setting::getValue('seo.'.$key, $fallback);
    }

    public function build(array $data): SeoMeta
    {
        $rawTitle = trim(strip_tags((string) ($data['title'] ?? '')));
        if ($rawTitle === '') {
            $rawTitle = (string) $this->global('default_title');
        }
        $title = $this->normalizeTitle($rawTitle);

        $rawDesc = trim(strip_tags((string) ($data['description'] ?? '')));
        if ($rawDesc === '') {
            $rawDesc = $this->global('default_description');
        }
        $description = $this->normalizeDescription($rawDesc);

        $canonical = trim((string) ($data['canonical'] ?? ''));
        if ($canonical === '') {
            $canonical = url()->current();
        }

        $ogResolved = $this->resolveOgImage((string) ($data['og_image'] ?? ''));
        $ogImage = $ogResolved['url'];

        $robots = trim((string) ($data['robots'] ?? ''));
        $robots = $robots !== '' ? $robots : $this->robotsDirective();

        return new SeoMeta([
            'title' => $title,
            'description' => $description,
            'keywords' => filled($data['keywords'] ?? null) ? (string) $data['keywords'] : null,
            'canonical' => $canonical,
            'og_image' => $ogImage,
            'og_image_width' => $ogResolved['width'],
            'og_image_height' => $ogResolved['height'],
            'og_image_type' => $ogResolved['type'],
            'robots' => $robots,
            'og_type' => (string) ($data['og_type'] ?? 'website'),
            'schema_extra' => $data['schema_extra'] ?? null,
        ]);
    }

    public function normalizeTitle(string $title, ?string $suffix = null): string
    {
        $title = preg_replace('/\s+/u', ' ', trim(strip_tags($title)));

        return $title !== '' ? $title : (string) $this->global('default_title');
    }

    public function normalizeDescription(string $description): string
    {
        $description = preg_replace('/\s+/u', ' ', trim(strip_tags($description)));
        $max = (int) config('seo.description_max', 160);

        return Str::limit($description, $max, '…');
    }

    public function shouldNoIndex(): bool
    {
        return $this->robotsDirective() !== null;
    }

    /**
     * Private/account pages stay nofollow. Filtered/search result URLs stay followable.
     */
    public function robotsDirective(): ?string
    {
        $routeName = optional(request()->route())->getName();
        if ($routeName) {
            foreach (config('seo.noindex_route_patterns', []) as $pattern) {
                if (Str::is($pattern, $routeName)) {
                    return 'noindex, nofollow';
                }
            }
        }

        if ($this->hasThinListingQuery()) {
            return 'noindex, follow';
        }

        return null;
    }

    public function hasThinListingQuery(): bool
    {
        if (! request()->routeIs(['shop.search', 'shop.menu', 'shop.products'])) {
            return false;
        }

        foreach (config('seo.listing_query_keys', []) as $key) {
            $val = request()->query($key);
            if ($val === null || $val === '' || $val === []) {
                continue;
            }

            return true;
        }

        return false;
    }

    public function absoluteUrl(?string $path): string
    {
        if (! $path) {
            return url('/');
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return url('/'.ltrim($path, '/'));
    }

    /**
     * WhatsApp/Facebook need a JPG/PNG around 1200x630. Wide header logos get cropped.
     *
     * @return array{url: string, width: int|null, height: int|null, type: string|null}
     */
    public function resolveOgImage(string $ogImage): array
    {
        $fallback = '/images/og-share.png';
        $ogImage = trim($ogImage);
        if ($ogImage === '') {
            $ogImage = trim((string) $this->global('default_og_image'));
        }
        if ($ogImage === '' || $this->isLocalUnusableOgImage($ogImage)) {
            $ogImage = $fallback;
        }

        $url = preg_match('#^https?://#i', $ogImage) ? $ogImage : $this->absoluteUrl($ogImage);
        $meta = $this->localOgImageMeta($ogImage) ?? $this->localOgImageMeta($fallback);

        return [
            'url' => $url,
            'width' => $meta['width'] ?? 1200,
            'height' => $meta['height'] ?? 630,
            'type' => $meta['type'] ?? 'image/png',
        ];
    }

    /**
     * @return array{width: int, height: int, type: string}|null
     */
    private function localOgImageMeta(string $ogImage): ?array
    {
        $full = $this->localPublicPath($ogImage);
        if (! $full) {
            return null;
        }
        $info = @getimagesize($full);
        if (! $info || ($info[0] ?? 0) < 1 || ($info[1] ?? 0) < 1) {
            return null;
        }

        return [
            'width' => (int) $info[0],
            'height' => (int) $info[1],
            'type' => (string) ($info['mime'] ?? 'image/png'),
        ];
    }

    private function isLocalUnusableOgImage(string $ogImage): bool
    {
        if (preg_match('#^https?://#i', $ogImage)) {
            $host = parse_url($ogImage, PHP_URL_HOST);
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
            if ($host && $appHost && strcasecmp((string) $host, (string) $appHost) !== 0) {
                return false;
            }
        }

        $full = $this->localPublicPath($ogImage);
        if (! $full) {
            return true;
        }
        if (strtolower((string) pathinfo($full, PATHINFO_EXTENSION)) === 'svg') {
            return true;
        }
        $info = @getimagesize($full);
        if (! $info || ($info[0] ?? 0) < 200 || ($info[1] ?? 0) < 200) {
            return true;
        }
        $ratio = $info[0] / max($info[1], 1);

        return $ratio > 3.2 || $ratio < 0.4;
    }

    private function localPublicPath(string $ogImage): ?string
    {
        $path = $ogImage;
        if (preg_match('#^https?://#i', $ogImage)) {
            $path = (string) (parse_url($ogImage, PHP_URL_PATH) ?? '');
        }
        $base = \App\Support\AppUrl::basePath();
        if ($base !== '' && str_starts_with($path, $base.'/')) {
            $path = substr($path, strlen($base));
        }
        $path = ltrim($path, '/');
        if ($path === '') {
            return null;
        }
        $full = public_path($path);

        return is_file($full) ? $full : null;
    }

    public function organizationSchema(): array
    {
        $org = config('seo.organization', []);
        $address = $org['address'] ?? [];

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $org['name'] ?? config('app.name'),
            'legalName' => $org['legal_name'] ?? null,
            'url' => $org['url'] ?? url('/'),
            'logo' => $this->absoluteUrl($org['logo'] ?? null),
            'email' => $org['email'] ?? null,
            'telephone' => $org['phone'] ?? null,
            'sameAs' => $org['same_as'] ?? [],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $address['street'] ?? null,
                'addressLocality' => $address['city'] ?? null,
                'addressRegion' => $address['region'] ?? null,
                'postalCode' => $address['postal_code'] ?? null,
                'addressCountry' => $address['country'] ?? null,
            ],
        ]);
    }

    public function localBusinessSchema(): array
    {
        $org = config('seo.organization', []);
        $geo = $org['geo'] ?? [];
        $address = $org['address'] ?? [];

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $org['name'] ?? config('app.name'),
            'image' => $this->absoluteUrl($org['logo'] ?? null),
            'url' => $org['url'] ?? url('/'),
            'telephone' => $org['phone'] ?? null,
            'email' => $org['email'] ?? null,
            'priceRange' => '₹₹',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $address['street'] ?? null,
                'addressLocality' => $address['city'] ?? null,
                'addressRegion' => $address['region'] ?? null,
                'postalCode' => $address['postal_code'] ?? null,
                'addressCountry' => $address['country'] ?? null,
            ],
            'geo' => isset($geo['latitude'], $geo['longitude']) ? [
                '@type' => 'GeoCoordinates',
                'latitude' => $geo['latitude'],
                'longitude' => $geo['longitude'],
            ] : null,
            'sameAs' => $org['same_as'] ?? [],
        ]);
    }

    public function websiteSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => config('seo.organization.name', config('app.name')),
            'url' => url('/'),
            'inLanguage' => 'en-IN',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => url('/search').'?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    public function breadcrumbSchema(array $items): array
    {
        $list = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => url('/'),
            ],
        ];

        $pos = 2;
        foreach ($items as $item) {
            $label = is_array($item) ? ($item['label'] ?? '') : (string) $item;
            $url = is_array($item) ? ($item['url'] ?? null) : null;
            if ($label === '') {
                continue;
            }
            $entry = [
                '@type' => 'ListItem',
                'position' => $pos,
                'name' => $label,
            ];
            if ($url) {
                $entry['item'] = $url;
            }
            $list[] = $entry;
            $pos++;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $list,
        ];
    }

    /**
     * Homepage FAQ rows for the accordion and FAQPage schema.
     *
     * @return array<int, array{question: string, answer: string, track_link?: bool}>
     */
    public function homepageFaqItems(): array
    {
        $fromAdmin = [];
        $json = $this->global('faq_schema_json');
        if (filled($json)) {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                $fromAdmin = array_values(array_filter($decoded, fn ($row) => filled($row['question'] ?? null) && filled($row['answer'] ?? null)));
            }
        }

        if (count($fromAdmin) >= 5) {
            return $fromAdmin;
        }

        return [
            [
                'question' => __('What is Devbhoomi Naturals?'),
                'answer' => __('Devbhoomi Naturals is an online store for pure Himalayan foods from Uttarakhand — millets, pahadi pulses, red rice, and spices sourced from hill farmers and packed for pan-India delivery.'),
            ],
            [
                'question' => __('Are Devbhoomi products organic and authentic?'),
                'answer' => __('Yes. We source traditional Uttarakhand staples such as Jhangora millet, Gahat dal, Kala Bhatt, Pahadi rajma, and Himalayan red rice from verified growers so you get clean, chemical-free pantry foods.'),
            ],
            [
                'question' => __('Which Himalayan products can I buy online?'),
                'answer' => __('Shop millets (Jhangora, Mandua/Ragi), pahadi pulses (Gahat, Bhatt, Rajma), Himalayan red rice, and spices. Browse Our Products to see the full Devbhoomi Naturals catalog.'),
            ],
            [
                'question' => __('Do you deliver across India?'),
                'answer' => __('Yes — we ship pan-India. Free delivery on every prepaid order.'),
            ],
            [
                'question' => __('Is delivery free on prepaid orders?'),
                'answer' => __('Yes. Free delivery on every prepaid order, anywhere in India. Extra prepaid discounts may also apply at checkout.'),
            ],
            [
                'question' => __('How can I track my Devbhoomi order?'),
                'answer' => __('Use Track order on the website with your order number and the email or mobile used at checkout. You can also follow updates from My orders after you sign in.'),
                'track_link' => true,
            ],
            [
                'question' => __('How should I store millets, pulses, and red rice?'),
                'answer' => __('Keep them in a cool, dry place in an airtight jar, away from sunlight. Himalayan grains and pulses stay fresh longer when transferred from the pack after opening.'),
            ],
            [
                'question' => __('How do I contact Devbhoomi Naturals for bulk or support?'),
                'answer' => __('Use the Contact us page or call +91 9217732670 for product questions, bulk orders, and delivery help. We are happy to guide you on pahadi staples and order status.'),
            ],
        ];
    }

    public function faqSchemaFromJson(?string $json): ?array
    {
        if (! filled($json)) {
            return $this->faqSchemaFromItems($this->homepageFaqItems());
        }
        $decoded = json_decode($json, true);
        if (! is_array($decoded) || empty($decoded)) {
            return $this->faqSchemaFromItems($this->homepageFaqItems());
        }

        return $this->faqSchemaFromItems($decoded);
    }

    public function faqSchemaFromItems(array $items): ?array
    {
        $entities = [];
        foreach ($items as $row) {
            $q = trim((string) ($row['question'] ?? ''));
            $a = trim(strip_tags((string) ($row['answer'] ?? '')));
            if ($q === '' || $a === '') {
                continue;
            }
            $entities[] = [
                '@type' => 'Question',
                'name' => $q,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $a,
                ],
            ];
        }

        if (empty($entities)) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $entities,
        ];
    }

    public function scorePage(array $checks): array
    {
        $score = 0;
        $max = 0;
        $issues = [];

        $rules = [
            'title_length' => ['weight' => 15, 'label' => 'Title length (50–60 chars)'],
            'description_length' => ['weight' => 15, 'label' => 'Description length (150–160 chars)'],
            'has_h1' => ['weight' => 10, 'label' => 'Single H1 present'],
            'has_canonical' => ['weight' => 10, 'label' => 'Canonical URL set'],
            'has_og_image' => ['weight' => 10, 'label' => 'Open Graph image'],
            'has_schema' => ['weight' => 10, 'label' => 'Structured data'],
            'images_have_alt' => ['weight' => 10, 'label' => 'Images have alt text'],
            'internal_links' => ['weight' => 10, 'label' => 'Internal linking'],
            'mobile_friendly' => ['weight' => 10, 'label' => 'Mobile viewport meta'],
        ];

        foreach ($rules as $key => $rule) {
            $max += $rule['weight'];
            $pass = ! empty($checks[$key]);
            if ($pass) {
                $score += $rule['weight'];
            } else {
                $issues[] = $rule['label'];
            }
        }

        $percent = $max > 0 ? (int) round(($score / $max) * 100) : 0;

        return [
            'score' => $percent,
            'issues' => $issues,
            'grade' => $percent >= 85 ? 'Good' : ($percent >= 60 ? 'Fair' : 'Needs work'),
        ];
    }

    public function canonicalForListing(string $baseUrl): string
    {
        return $baseUrl;
    }

    /**
     * @param  \Illuminate\Contracts\Pagination\LengthAwarePaginator  $paginator
     * @return array{prev: ?string, next: ?string}
     */
    public function paginationHeadLinks($paginator): array
    {
        return [
            'prev' => $paginator->previousPageUrl(),
            'next' => $paginator->nextPageUrl(),
        ];
    }

    public function itemListSchema(string $name, array $productUrls): ?array
    {
        if (empty($productUrls)) {
            return null;
        }

        $items = [];
        $pos = 1;
        foreach (array_slice($productUrls, 0, 20) as $url) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $pos,
                'url' => $url,
            ];
            $pos++;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $name,
            'numberOfItems' => count($productUrls),
            'itemListElement' => $items,
        ];
    }

    public function webPageSchema(string $name, string $url, ?string $description = null): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $name,
            'url' => $url,
            'description' => $description,
            'inLanguage' => 'en-IN',
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => config('seo.organization.name', config('app.name')),
                'url' => url('/'),
            ],
        ]);
    }
}
