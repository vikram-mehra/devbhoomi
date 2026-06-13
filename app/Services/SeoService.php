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
            'site_title_suffix' => config('seo.default_title_suffix'),
            'default_description' => config('seo.default_description'),
            'default_keywords' => config('seo.default_keywords'),
            'default_og_image' => config('seo.default_og_image'),
            'google_analytics_id' => 'G-XLTR42JC0T',
            'twitter_handle' => '',
            'facebook_app_id' => '',
            'faq_schema_json' => '',
        ];

        $fallback = $map[$key] ?? $default;

        return Setting::getValue('seo.'.$key, $fallback);
    }

    public function build(array $data): SeoMeta
    {
        $suffix = $this->global('site_title_suffix');
        $rawTitle = trim(strip_tags((string) ($data['title'] ?? '')));
        $title = $this->normalizeTitle($rawTitle, $suffix);

        $rawDesc = trim(strip_tags((string) ($data['description'] ?? '')));
        if ($rawDesc === '') {
            $rawDesc = $this->global('default_description');
        }
        $description = $this->normalizeDescription($rawDesc);

        $canonical = trim((string) ($data['canonical'] ?? ''));
        if ($canonical === '') {
            $canonical = url()->current();
        }

        $ogImage = trim((string) ($data['og_image'] ?? ''));
        if ($ogImage === '') {
            $ogImage = $this->absoluteUrl($this->global('default_og_image'));
        } elseif (! preg_match('#^https?://#i', $ogImage)) {
            $ogImage = $this->absoluteUrl($ogImage);
        }

        $robots = $data['robots'] ?? null;
        if ($robots === null && $this->shouldNoIndex()) {
            $robots = 'noindex, nofollow';
        }

        return new SeoMeta([
            'title' => $title,
            'description' => $description,
            'keywords' => filled($data['keywords'] ?? null) ? (string) $data['keywords'] : null,
            'canonical' => $canonical,
            'og_image' => $ogImage,
            'robots' => $robots,
            'og_type' => (string) ($data['og_type'] ?? 'website'),
            'schema_extra' => $data['schema_extra'] ?? null,
        ]);
    }

    public function normalizeTitle(string $title, ?string $suffix = null): string
    {
        $suffix = $suffix ?: $this->global('site_title_suffix');
        $title = preg_replace('/\s+/u', ' ', trim(strip_tags($title))) ?: $suffix;
        $min = (int) config('seo.title_min', 50);
        $max = (int) config('seo.title_max', 60);

        if (mb_strlen($title) > $max) {
            return Str::limit($title, $max, '');
        }

        if (mb_strlen($title) < $min && $suffix && ! Str::contains($title, $suffix)) {
            $candidate = $title.' | '.$suffix;
            if (mb_strlen($candidate) <= $max) {
                return $candidate;
            }
        }

        return $title;
    }

    public function normalizeDescription(string $description): string
    {
        $description = preg_replace('/\s+/u', ' ', trim(strip_tags($description)));
        $max = (int) config('seo.description_max', 160);

        return Str::limit($description, $max, '…');
    }

    public function shouldNoIndex(): bool
    {
        $routeName = optional(request()->route())->getName();
        if (! $routeName) {
            return false;
        }

        foreach (config('seo.noindex_route_patterns', []) as $pattern) {
            if (Str::is($pattern, $routeName)) {
                return true;
            }
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

    public function faqSchemaFromJson(?string $json): ?array
    {
        if (! filled($json)) {
            return null;
        }
        $decoded = json_decode($json, true);
        if (! is_array($decoded) || empty($decoded)) {
            return null;
        }

        $entities = [];
        foreach ($decoded as $row) {
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
        $query = request()->query();
        unset($query['page'], $query['sort']);

        if (empty($query)) {
            return $baseUrl;
        }

        return $baseUrl.'?'.http_build_query($query);
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
}
