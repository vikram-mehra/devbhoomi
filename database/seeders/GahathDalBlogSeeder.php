<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class GahathDalBlogSeeder extends Seeder
{
    public function run(): void
    {
        if (BlogPost::where('slug', 'gahath-dal-benefits')->exists()) {
            $this->command?->info('Blog post already exists: gahath-dal-benefits');

            return;
        }

        $gahat = url('/product/pahadi-gahat-dal-bnwm');
        $bhatt = url('/product/pahadi-bhatt-biui');
        $redRice = url('/product/pahadi-red-rice-ym0f');
        $pulses = url('/menu/pahadi-pulses');
        $himalayan = url('/menu/uttarakhand-food-products');
        $contact = url('/contact-us');

        $imagePath = 'blog/gahath-dal-benefits.png';
        if (! is_file(public_path('storage/'.$imagePath))) {
            $imagePath = null;
        }

        $body = <<<HTML
<p>Gahath Dal, also known as Horse Gram or Kulthi, has been a part of Himalayan cuisine for generations. Today, as people search for natural and nutrient-rich foods, Gahath Dal is gaining recognition as a powerful traditional superfood — one that hill families never forgot, even when the rest of India moved toward polished dals and instant meals.</p>
<p>At Devbhoomi Naturals, our <a href="{$gahat}">Pahadi Gahat Dal</a> comes from Uttarakhand's high-altitude farms, where this hardy lentil has been grown without chemicals for centuries. This guide explains why gahath deserves a comeback in your kitchen.</p>

<h2>Why Gahath Dal is Special</h2>
<p>Unlike heavily processed foods, Gahath Dal is a simple, wholesome ingredient packed with natural nutrition. It grows where other crops struggle — on marginal land, with minimal water — and still delivers one of the highest protein concentrations among Indian pulses.</p>
<p>In Garhwal and Kumaon, gahath is not a health trend. It is winter food, festival food, and recovery food after long days of physical labour in the mountains.</p>

<h2>Rich Source of Plant Protein</h2>
<p>Protein supports muscle maintenance and daily nutritional needs. Gahath dal contains significantly more protein than arhar or masoor, making it invaluable for vegetarians, athletes, and anyone building meals around plant-based nutrition.</p>
<p>A single bowl of well-cooked gahath with <a href="{$redRice}">Pahadi Red Rice</a> provides a complete amino acid profile when paired with grains — the same principle behind classic dal-chawal, amplified.</p>

<h2>High Fiber Content</h2>
<p>Fiber contributes to satiety and digestive health. Gahath's dense fiber structure keeps you full longer, supports regular bowel movement, and feeds beneficial gut bacteria when cooked thoroughly after soaking.</p>

<h2>Naturally Nutrient Dense</h2>
<p>Gahath Dal contains:</p>
<ul>
<li>Iron — supports oxygen transport and energy</li>
<li>Calcium — contributes to bone health</li>
<li>Protein — among the highest in the pulse family</li>
<li>Fiber — for digestion and weight management</li>
<li>Essential minerals — phosphorus, molybdenum, and more</li>
</ul>
<p>These nutrients arrive together in one ingredient — not from supplements, but from soil, season, and traditional farming.</p>

<h2>Popular Traditional Uses</h2>

<h3>Gahath Dal Soup</h3>
<p>A comforting Himalayan recipe traditionally enjoyed during winter. Soaked gahath is pressure cooked with turmeric, garlic, and cumin, then finished with coriander and lemon. Light on the stomach yet deeply nourishing — hill families serve this to anyone recovering from illness or cold weather fatigue.</p>

<h3>Gahath Dal Curry</h3>
<p>A hearty meal paired with rice or rotis. The dal is slow-cooked with onion, tomato, ginger, and local spices until creamy. In Kumaon, gahath curry with steamed rice is Sunday lunch in many homes — simple, honest, and satisfying.</p>

<h3>Gahath Ke Pakode</h3>
<p>Soaked and coarsely ground gahath mixed with spices, shaped into fritters, and shallow fried. A festival snack that turns this superfood into celebration food without losing its nutrition.</p>

<h2>Why Modern Consumers Love Gahath Dal</h2>

<h3>Supports Active Lifestyles</h3>
<p>The nutrient profile makes it suitable for people seeking healthy food choices. Gym-goers, hikers, and busy professionals are rediscovering gahath because it delivers sustained energy without processed additives.</p>

<h3>Traditional Meets Modern</h3>
<p>Gahath Dal fits well into modern healthy eating patterns. Use it in Buddha bowls, protein-rich salads, or meal-prep containers alongside roasted vegetables. The traditional ingredient adapts to contemporary kitchens without losing its identity.</p>

<h3>Winter Wellness Food</h3>
<p>Ayurvedic and folk traditions in Uttarakhand associate gahath with body warmth and kidney health. While not a medical treatment, its role in seasonal wellness diets has persisted for good reason — it works as everyday nourishment.</p>

<h2>Gahath vs Common Dals</h2>
<ul>
<li><strong>Protein:</strong> Higher than masoor and arhar</li>
<li><strong>Cooking:</strong> Requires overnight soak and longer pressure cooking</li>
<li><strong>Flavor:</strong> Earthy, robust — pairs well with bold spices</li>
<li><strong>Season:</strong> Peak consumption in winter; excellent year-round</li>
</ul>
<p>Rotate gahath with <a href="{$bhatt}">Kala Bhatt</a> and rajma through the week for diverse plant protein sources.</p>

<h2>How to Cook Pahadi Gahat Dal</h2>
<ol>
<li>Rinse 1 cup gahath and soak overnight (10–12 hours).</li>
<li>Discard soak water. Pressure cook with fresh water, salt, and turmeric (6–7 whistles).</li>
<li>Temper ghee with cumin, garlic, dry red chilli, and jamboo if available.</li>
<li>Simmer 15 minutes. Serve with <a href="{$redRice}">Pahadi Red Rice</a> or millet.</li>
</ol>
<p>Shop authentic <a href="{$gahat}">Pahadi Gahat Dal</a> from our <a href="{$pulses}">Pahadi-Pulses</a> collection.</p>

<h2>FAQ</h2>

<h3>Can Gahath Dal be included in weight management diets?</h3>
<p>Its protein and fiber content may help promote fullness. Replacing one heavy dinner a week with gahath soup or curry can reduce overall calorie intake without feeling deprived — when combined with vegetables and controlled portions.</p>

<h3>Is it suitable for daily consumption?</h3>
<p>It can be enjoyed regularly as part of a balanced diet. Most nutritionists recommend rotating pulses rather than eating the same dal daily — include gahath 2–3 times per week alongside other dals from our <a href="{$himalayan}">Uttarakhand food range</a>.</p>

<h3>Is Gahath Dal the same as Horse Gram?</h3>
<p>Yes. Gahath, gahat, kulthi, and horse gram refer to the same lentil (<em>Macrotyloma uniflorum</em>). Pahadi Gahat from Uttarakhand hills is prized for aroma and cooking quality.</p>

<h3>How should I store dried Gahath Dal?</h3>
<p>Keep in an airtight container in a cool, dry place. Use within 8–12 months. Avoid moisture and direct sunlight.</p>

<h2>Conclusion</h2>
<p>Gahath Dal combines tradition, nutrition, and taste. It is one of the most underrated foods from Uttarakhand and deserves a place in every healthy kitchen — whether you cook it the way your grandmother did or adapt it for modern meal prep.</p>
<p><strong>Rediscover this Himalayan superfood.</strong> Order <a href="{$gahat}">Pahadi Gahat Dal</a> from Devbhoomi Naturals. Pair with <a href="{$bhatt}">Kala Bhatt</a> and <a href="{$redRice}">Pahadi Red Rice</a> for a complete pahadi pantry. <strong>Free delivery on prepaid orders above ₹499.</strong> <a href="{$contact}">Contact us</a> at +91 9217732670.</p>
HTML;

        BlogPost::create([
            'title' => 'Gahath Dal: The Forgotten Himalayan Superfood for Modern Health',
            'slug' => 'gahath-dal-benefits',
            'meta_title' => 'Gahath Dal Benefits | Himalayan Horse Gram',
            'meta_description' => 'Learn why Gahath Dal is becoming popular again for its nutrition, protein, fiber, and traditional Himalayan wellness benefits.',
            'meta_keywords' => 'Gahath Dal Benefits, horse gram benefits, kulthi dal, Pahadi Gahat Dal Uttarakhand',
            'excerpt' => 'Gahath Dal (horse gram) is Uttarakhand’s forgotten superfood — rich in protein, fiber, and minerals. Learn benefits, recipes, and why modern diets need this Himalayan pulse.',
            'body' => $body,
            'image' => $imagePath,
            'published_at' => now(),
            'is_published' => true,
            'sort_order' => 0,
        ]);

        Cache::forget('home.blog_posts');

        $this->command?->info('Blog post created: gahath-dal-benefits');
    }
}
