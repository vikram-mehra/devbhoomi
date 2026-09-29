<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogPostSeeder extends Seeder
{
    public function run()
    {
        if (BlogPost::exists()) {
            return;
        }

        $blogBodies = [
            "Shopping online is convenient, but sizing can feel like guesswork. Start by measuring yourself with a soft tape and compare against the seller's size chart — not your usual label from another brand.\n\nRead recent reviews that mention fit: words like \"runs small\" or \"true to size\" are gold. When in doubt, pick the size that matches your largest measurement (often chest or hips).\n\nFinally, check return policies before you buy. A flexible return window makes it safer to try two sizes if you're between chart lines.",
            "Marketplaces bring many sellers together — quality varies. Look for shops with a solid rating history and detailed product photos (not only stock images).\n\nPay attention to shipping timelines and where items ship from. Clear policies on returns and warranties usually mean the seller stands behind what they sell.\n\nUse secure checkout on the platform, avoid off-site payment requests, and save order confirmations. If something looks too cheap to be true, compare specs and reviews across similar listings.",
            "Building a wardrobe doesn't require a huge budget. Focus on versatile basics: solid tees, one good pair of jeans, and layering pieces you can mix for work and weekends.\n\nWatch for bundle deals and seasonal sales on staples. Neutral colours stretch further than loud prints when you're building from scratch.\n\nQuality over quantity: one well-made item that fits well will get more wears than three impulse buys. Set a simple monthly cap and shop with a short list.",
        ];

        foreach ([
            ['How to pick the right size online', 'how-to-pick-the-right-size-online', $blogBodies[0]],
            ['5 ways to spot trusted sellers', '5-ways-to-spot-trusted-sellers', $blogBodies[1]],
            ['Wardrobe staples under ₹999', 'wardrobe-staples-under-999', $blogBodies[2]],
        ] as $i => $row) {
            BlogPost::create([
                'title' => $row[0],
                'slug' => $row[1],
                'excerpt' => Str::limit(strip_tags($row[2]), 180),
                'body' => $row[2],
                'image' => null,
                'published_at' => now()->subDays(2 - $i),
                'is_published' => true,
                'sort_order' => $i,
            ]);
        }
    }
}
