<?php

namespace App\Models;

use App\Support\PublicImagePath;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AboutGalleryItem extends Model
{
    use PublicImagePath;

    protected $fillable = ['about_page_id', 'image', 'caption', 'sort_order'];

    public function aboutPage(): BelongsTo
    {
        return $this->belongsTo(AboutPage::class);
    }

    public function imageUrl(): string
    {
        return $this->publicImageUrl($this->image, 'gallery-'.$this->id);
    }
}
