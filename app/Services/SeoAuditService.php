<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\MenuItem;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Support\Str;

class SeoAuditService
{
    /** @var SeoService */
    private $seo;

    public function __construct(SeoService $seo)
    {
        $this->seo = $seo;
    }

    public function run(): array
    {
        $issues = [];

        $issues = array_merge($issues, $this->auditProducts());
        $issues = array_merge($issues, $this->auditMenuItems());
        $issues = array_merge($issues, $this->auditBlogPosts());
        $issues = array_merge($issues, $this->auditVendors());
        $issues = array_merge($issues, $this->auditGlobal());

        usort($issues, function ($a, $b) {
            $order = ['high' => 0, 'medium' => 1, 'low' => 2];

            return ($order[$a['priority']] ?? 9) <=> ($order[$b['priority']] ?? 9);
        });

        return $issues;
    }

    public function applyAutoFixes(): array
    {
        $fixed = [];

        Product::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('meta_description')->orWhere('meta_description', '');
            })
            ->chunkById(50, function ($products) use (&$fixed) {
                foreach ($products as $product) {
                    $desc = Str::limit(strip_tags($product->short_description ?: $product->description ?: $product->name), 160, '');
                    if ($desc !== '') {
                        $product->update(['meta_description' => $desc]);
                        $fixed[] = 'Product #'.$product->id.': meta description generated';
                    }
                }
            });

        Product::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('meta_title')->orWhere('meta_title', '');
            })
            ->chunkById(50, function ($products) use (&$fixed) {
                foreach ($products as $product) {
                    $product->update(['meta_title' => $this->seo->normalizeTitle($product->name)]);
                    $fixed[] = 'Product #'.$product->id.': meta title generated';
                }
            });

        MenuItem::query()
            ->where('is_active', true)
            ->whereNotNull('slug')
            ->where(function ($q) {
                $q->whereNull('meta_description')->orWhere('meta_description', '');
            })
            ->each(function (MenuItem $item) use (&$fixed) {
                $item->update([
                    'meta_description' => $this->seo->normalizeDescription(
                        'Shop '.$item->title.' online — pure Himalayan organic products from Devbhoomi Naturals, Uttarakhand.'
                    ),
                ]);
                $fixed[] = 'Menu "'.$item->title.'": meta description generated';
            });

        BlogPost::published()
            ->where(function ($q) {
                $q->whereNull('meta_description')->orWhere('meta_description', '');
            })
            ->each(function (BlogPost $post) use (&$fixed) {
                $desc = Str::limit(strip_tags($post->excerpt ?: $post->body), 160, '');
                if ($desc !== '') {
                    $post->update(['meta_description' => $desc]);
                    $fixed[] = 'Blog "'.$post->title.'": meta description generated';
                }
            });

        BlogPost::published()
            ->where(function ($q) {
                $q->whereNull('meta_title')->orWhere('meta_title', '');
            })
            ->each(function (BlogPost $post) use (&$fixed) {
                $post->update(['meta_title' => $this->seo->normalizeTitle($post->title)]);
                $fixed[] = 'Blog "'.$post->title.'": meta title generated';
            });

        MenuItem::query()
            ->where('is_active', true)
            ->whereNotNull('slug')
            ->where(function ($q) {
                $q->whereNull('meta_title')->orWhere('meta_title', '');
            })
            ->each(function (MenuItem $item) use (&$fixed) {
                $item->update(['meta_title' => $this->seo->normalizeTitle($item->title.' — Organic '.$item->title)]);
                $fixed[] = 'Menu "'.$item->title.'": meta title generated';
            });

        Vendor::query()
            ->where('status', 'approved')
            ->where(function ($q) {
                $q->whereNull('meta_description')->orWhere('meta_description', '');
            })
            ->each(function (Vendor $vendor) use (&$fixed) {
                $desc = Str::limit(strip_tags($vendor->description ?: 'Shop organic products from '.$vendor->shop_name), 160, '');
                if ($desc !== '') {
                    $vendor->update(['meta_description' => $desc]);
                    $fixed[] = 'Vendor "'.$vendor->shop_name.'": meta description generated';
                }
            });

        return $fixed;
    }

    private function auditProducts(): array
    {
        $issues = [];

        Product::query()->where('is_active', true)->each(function (Product $p) use (&$issues) {
            $url = route('product.show', $p->slug);

            if (! filled($p->meta_title)) {
                $issues[] = $this->issue('high', 'Missing meta title', $p->name, $url, 'Set meta title in product admin or run auto-fix.');
            } elseif (mb_strlen($p->meta_title) < 30 || mb_strlen($p->meta_title) > 70) {
                $issues[] = $this->issue('medium', 'Meta title length suboptimal', $p->name, $url, 'Aim for 50–60 characters.');
            }

            if (! filled($p->meta_description)) {
                $issues[] = $this->issue('high', 'Missing meta description', $p->name, $url, 'Add a 150–160 character description.');
            }

            if (! filled($p->slug)) {
                $issues[] = $this->issue('high', 'Missing product slug', $p->name, $url, 'Slug is required for SEO-friendly URLs.');
            }

            if (! $p->images()->exists()) {
                $issues[] = $this->issue('medium', 'Product has no images', $p->name, $url, 'Add product images with descriptive alt text.');
            }
        });

        return $issues;
    }

    private function auditMenuItems(): array
    {
        $issues = [];

        MenuItem::query()->where('is_active', true)->whereNotNull('slug')->each(function (MenuItem $m) use (&$issues) {
            if ($m->isBuiltInPage()) {
                return;
            }
            $url = route('shop.menu', $m->slug);

            if (! filled($m->meta_description)) {
                $issues[] = $this->issue('high', 'Category missing meta description', $m->title, $url, 'Add SEO fields in Menu admin.');
            }
            if (! filled($m->meta_title)) {
                $issues[] = $this->issue('medium', 'Category missing meta title', $m->title, $url, 'Add a unique title for this collection.');
            }
        });

        return $issues;
    }

    private function auditBlogPosts(): array
    {
        $issues = [];

        BlogPost::published()->each(function (BlogPost $post) use (&$issues) {
            $url = route('blog.show', $post->slug);

            if (! filled($post->meta_description) && ! filled($post->excerpt)) {
                $issues[] = $this->issue('medium', 'Blog post missing description', $post->title, $url, 'Add excerpt or meta description.');
            }
            if (! filled($post->image)) {
                $issues[] = $this->issue('low', 'Blog post missing cover image', $post->title, $url, 'Upload a cover image for social sharing.');
            }
        });

        return $issues;
    }

    private function auditVendors(): array
    {
        $issues = [];

        Vendor::query()->where('status', 'approved')->each(function (Vendor $v) use (&$issues) {
            $url = route('vendor.shop', $v->slug);

            if (! filled($v->meta_description) && ! filled($v->description)) {
                $issues[] = $this->issue('low', 'Vendor shop missing description', $v->shop_name, $url, 'Add shop description for SEO.');
            }
        });

        return $issues;
    }

    private function auditGlobal(): array
    {
        $issues = [];
        $sitemapUrl = url('/sitemap.xml');
        $robotsPath = public_path('robots.txt');

        if (! is_readable($robotsPath) || ! Str::contains(file_get_contents($robotsPath), 'Sitemap:')) {
            $issues[] = $this->issue('medium', 'robots.txt missing Sitemap directive', 'robots.txt', url('/robots.txt'), 'Add Sitemap: '.$sitemapUrl);
        }

        if (! filled($this->seo->global('default_description'))) {
            $issues[] = $this->issue('high', 'Global default meta description not set', 'Site-wide', url('/'), 'Configure in Admin → SEO Settings.');
        }

        return $issues;
    }

    private function issue(string $priority, string $type, string $entity, string $url, string $fix): array
    {
        return compact('priority', 'type', 'entity', 'url', 'fix');
    }
}
