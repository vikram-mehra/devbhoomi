<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProductReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviewers = [
            ['name' => 'Ananya Joshi', 'email' => 'reviews.ananya@devbhoominaturals.local'],
            ['name' => 'Rohan Negi', 'email' => 'reviews.rohan@devbhoominaturals.local'],
            ['name' => 'Meera Bisht', 'email' => 'reviews.meera@devbhoominaturals.local'],
            ['name' => 'Vikram Rawat', 'email' => 'reviews.vikram@devbhoominaturals.local'],
            ['name' => 'Kavita Shah', 'email' => 'reviews.kavita@devbhoominaturals.local'],
            ['name' => 'Suresh Pant', 'email' => 'reviews.suresh@devbhoominaturals.local'],
            ['name' => 'Pooja Bhandari', 'email' => 'reviews.pooja@devbhoominaturals.local'],
            ['name' => 'Amit Semwal', 'email' => 'reviews.amit@devbhoominaturals.local'],
            ['name' => 'Neha Rana', 'email' => 'reviews.neha@devbhoominaturals.local'],
            ['name' => 'Deepak Farswan', 'email' => 'reviews.deepak@devbhoominaturals.local'],
            ['name' => 'Shalini Tiwari', 'email' => 'reviews.shalini@devbhoominaturals.local'],
            ['name' => 'Manish Bhatt', 'email' => 'reviews.manish@devbhoominaturals.local'],
            ['name' => 'Ritika Chauhan', 'email' => 'reviews.ritika@devbhoominaturals.local'],
            ['name' => 'Yogesh Koranga', 'email' => 'reviews.yogesh@devbhoominaturals.local'],
            ['name' => 'Priya Nautiyal', 'email' => 'reviews.priya@devbhoominaturals.local'],
            ['name' => 'Harshita Dangwal', 'email' => 'reviews.harshita@devbhoominaturals.local'],
            ['name' => 'Karan Bisht', 'email' => 'reviews.karan@devbhoominaturals.local'],
            ['name' => 'Sunita Rawat', 'email' => 'reviews.sunita@devbhoominaturals.local'],
        ];

        $users = collect($reviewers)->map(function (array $row) {
            return User::query()->firstOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => Hash::make(Str::random(24)),
                    'role' => User::ROLE_USER,
                    'account_status' => User::ACCOUNT_ACTIVE,
                    'email_verified_at' => now(),
                ]
            );
        })->values();

        Review::query()
            ->whereIn('user_id', $users->pluck('id'))
            ->delete();

        $templates = [
            ['rating' => 5, 'title' => 'Fresh and authentic', 'body' => 'Tastes clean and earthy — just like produce from the hills. Packing was neat and the quality is exactly what we hoped for.'],
            ['rating' => 5, 'title' => 'Will buy again', 'body' => 'We used this in our weekly cooking and everyone noticed the flavour. Genuine Himalayan goodness, no extra processing taste.'],
            ['rating' => 4, 'title' => 'Good quality pack', 'body' => 'Nice aroma and texture. Delivery was on time. A little pricey, but the purity makes it worth repeating.'],
            ['rating' => 5, 'title' => 'Trusted pantry staple', 'body' => 'This has become a regular in our kitchen. Clean grains, no dust, and the taste is richer than the usual store packs.'],
            ['rating' => 4, 'title' => 'As described', 'body' => 'Looks and cooks as expected. Fresh batch, well sealed. Happy to recommend it to family.'],
            ['rating' => 5, 'title' => 'Pure Himalayan taste', 'body' => 'You can tell this is carefully sourced. Soft after cooking and very wholesome. Excellent for everyday meals.'],
            ['rating' => 5, 'title' => 'Family loved it', 'body' => 'Cooked this for Sunday lunch and it disappeared quickly. Mild aroma, no stones, and the finish is really clean.'],
            ['rating' => 4, 'title' => 'Better than local store', 'body' => 'Compared with what we usually buy nearby — this feels fresher and more consistent. Will keep it in our monthly order.'],
            ['rating' => 5, 'title' => 'Great for daily cooking', 'body' => 'We added it to dal and rotis. Flavour stays natural and the pack stays fresh after opening too.'],
            ['rating' => 3, 'title' => 'Decent, not outstanding', 'body' => 'Quality is fine and packaging is good. Taste is natural, just not as strong as I expected. Still usable every week.'],
        ];

        $products = Product::query()->where('is_active', true)->orderBy('id')->get();
        $counts = [4, 5, 6, 7, 8];

        foreach ($products as $index => $product) {
            $needed = $counts[$index % count($counts)];
            $existing = Review::query()
                ->where('product_id', $product->id)
                ->where('is_approved', true)
                ->count();
            $toAdd = max(0, $needed - $existing);
            if ($toAdd === 0) {
                $this->refreshProductRating($product);

                continue;
            }

            $pickedUsers = $users->shuffle()->take($toAdd);
            $pickedTemplates = collect($templates)->shuffle()->values();

            foreach ($pickedUsers as $userIndex => $user) {
                $template = $pickedTemplates[$userIndex % $pickedTemplates->count()];

                Review::query()->create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'order_id' => null,
                    'rating' => $template['rating'],
                    'title' => $template['title'],
                    'body' => $template['body'],
                    'is_approved' => true,
                    'created_at' => now()->subDays(random_int(2, 48)),
                    'updated_at' => now(),
                ]);
            }

            $this->refreshProductRating($product);
        }
    }

    private function refreshProductRating(Product $product): void
    {
        $stats = Review::query()
            ->where('product_id', $product->id)
            ->where('is_approved', true)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total')
            ->first();

        $product->update([
            'rating_avg' => round((float) ($stats->avg_rating ?? 0), 2),
            'rating_count' => (int) ($stats->total ?? 0),
        ]);
    }
}
