<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'product_id', 'user_id', 'order_id', 'rating', 'title', 'body', 'is_approved',
    ];

    protected $casts = ['is_approved' => 'boolean'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdOnLabel(): string
    {
        $date = $this->created_at;
        if (! $date) {
            return '';
        }

        $day = (int) $date->format('j');
        $mod100 = $day % 100;
        $suffix = match (true) {
            $mod100 >= 11 && $mod100 <= 13 => 'th',
            $day % 10 === 1 => 'st',
            $day % 10 === 2 => 'nd',
            $day % 10 === 3 => 'rd',
            default => 'th',
        };

        return $date->format('d').$suffix.' '.$date->format('M Y');
    }
}
