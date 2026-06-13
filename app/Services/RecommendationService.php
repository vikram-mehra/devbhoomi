<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class RecommendationService
{
    public function forYou(?User $user): \Illuminate\Database\Eloquent\Collection
    {
        $key = $user ? 'rec.user.'.$user->id : 'rec.guest';

        return Cache::remember($key, 300, function () use ($user) {
            $q = Product::query()->with(['images', 'vendor', 'variants', 'flashSale', 'menuItem'])
                ->where('is_active', true);

            return $q->orderByDesc('sales_count')->orderByDesc('rating_avg')->take(8)->get();
        });
    }

    public function aiPick(?User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $this->forYou($user);
    }
}
