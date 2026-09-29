<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AboutHighlight extends Model
{
    protected $fillable = ['about_page_id', 'icon', 'label', 'value', 'sort_order'];

    public function aboutPage(): BelongsTo
    {
        return $this->belongsTo(AboutPage::class);
    }
}
