<?php

namespace App\Support;

class SeoMeta
{
    /** @var string */
    public $title;

    /** @var string */
    public $description;

    /** @var string|null */
    public $keywords;

    /** @var string */
    public $canonical;

    /** @var string|null */
    public $ogImage;

    /** @var string|null */
    public $robots;

    /** @var string */
    public $ogType;

    /** @var array|null */
    public $schemaExtra;

    public function __construct(array $data = [])
    {
        $this->title = (string) ($data['title'] ?? '');
        $this->description = (string) ($data['description'] ?? '');
        $this->keywords = isset($data['keywords']) ? (string) $data['keywords'] : null;
        $this->canonical = (string) ($data['canonical'] ?? url()->current());
        $this->ogImage = isset($data['og_image']) ? (string) $data['og_image'] : null;
        $this->robots = isset($data['robots']) ? (string) $data['robots'] : null;
        $this->ogType = (string) ($data['og_type'] ?? 'website');
        $this->schemaExtra = $data['schema_extra'] ?? null;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'keywords' => $this->keywords,
            'canonical' => $this->canonical,
            'og_image' => $this->ogImage,
            'robots' => $this->robots,
            'og_type' => $this->ogType,
        ];
    }
}
