<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class HimalayanRedRiceBlogSeeder extends Seeder
{
    public function run(): void
    {
        if (BlogPost::where('slug', 'himalayan-red-rice-benefits')->exists()) {
            $this->command?->info('Blog post already exists: himalayan-red-rice-benefits');

            return;
        }

        $redRice = url('/product/pahadi-red-rice-ym0f');
        $rajma = url('/product/pahadi-rajma-kn1e');
        $bhatt = url('/product/pahadi-bhatt-biui');
        $rajmaBlog = url('/blog/pahadi-red-rajma-benefits');
        $bhattBlog = url('/blog/kala-bhatt-benefits');
        $himalayan = url('/menu/uttarakhand-food-products');
        $contact = url('/contact-us');

        $imagePath = 'blog/himalayan-red-rice-benefits.png';
        if (! is_file(public_path('storage/'.$imagePath))) {
            $imagePath = null;
        }

        $body = <<<HTML
<p>Red Rice has long been consumed in traditional Himalayan communities. Known for its distinctive color and rich flavor, it is now gaining popularity among health-conscious consumers who want more from every meal than empty calories.</p>
<p>Unlike polished white rice stripped of bran and germ, <a href="{$redRice}">Pahadi Red Rice</a> from Devbhoomi Naturals arrives as a whole grain — earthy, nutty, and rooted in Uttarakhand's hill agriculture.</p>

<h2>Nutritional Highlights</h2>
<p>Red Rice contains a naturally balanced package of nutrients that polished grains simply cannot match:</p>
<ul>
<li><strong>Fiber</strong> — supports digestion and keeps you fuller longer</li>
<li><strong>Iron</strong> — important for energy and healthy blood</li>
<li><strong>Natural antioxidants</strong> — from pigments that give the grain its color</li>
<li><strong>Essential minerals</strong> — including magnesium, zinc, and B vitamins</li>
</ul>
<p>Because the bran layer stays intact, red rice releases energy more gradually than white rice — a practical advantage for everyday meals, not just special diets.</p>

<h2>Benefits of Red Rice</h2>

<h3>Supports Balanced Nutrition</h3>
<p>It provides a combination of carbohydrates and valuable nutrients in one staple food. A bowl of red rice with dal or vegetables delivers complex carbs, plant protein, and micronutrients without relying on fortified or processed alternatives.</p>

<h3>Rich Flavor and Texture</h3>
<p>Red Rice offers a unique taste compared to ordinary white rice. Expect a slightly chewy bite, a warm reddish hue on the plate, and a nutty depth that pairs beautifully with pahadi dals, curries, and simple ghee-seasoned sides.</p>

<h3>Traditional and Natural</h3>
<p>It remains one of the most authentic grains of Himalayan agriculture. Hill farmers have grown red rice on terraced fields for generations — long before "superfood" became a marketing term. Choosing red rice means supporting that heritage while eating closer to the source.</p>

<h2>Why Red Rice Beats Polished White Rice</h2>
<ul>
<li><strong>More fiber</strong> — the bran layer stays on the grain</li>
<li><strong>More minerals</strong> — iron and magnesium are concentrated in the outer layers</li>
<li><strong>Lower glycemic impact</strong> — slower energy release compared to white rice</li>
<li><strong>Real flavor</strong> — no need to overload with spices to make a meal satisfying</li>
</ul>
<p>For families making one simple swap, replacing white rice with red rice even a few days a week can meaningfully improve overall diet quality.</p>

<h2>Cooking Ideas</h2>
<p>Red rice is versatile once you adjust water and cooking time slightly. Try these pahadi-inspired meals:</p>

<h3>Red Rice Pulao</h3>
<p>Cook soaked red rice with cumin, bay leaf, peas, and carrots. The grain holds its shape well and absorbs spices without turning mushy.</p>

<h3>Traditional Dal-Rice Meals</h3>
<p>The classic hill combination: red rice with <a href="{$rajma}">Pahadi Red Rajma</a> or gahath dal. Soak rajma overnight, pressure cook until soft, and serve over steaming red rice — comfort food with real nutrition.</p>

<h3>Grain Bowls</h3>
<p>Top cooked red rice with sautéed seasonal vegetables, a spoon of curd, and roasted seeds for a balanced lunch bowl.</p>

<h3>Healthy Lunch Recipes</h3>
<p>Pair red rice with <a href="{$bhatt}">Kala Bhatt (Pahadi Bhatt)</a> curry for a protein-rich vegetarian plate. Leftover rice works well next day as lemon rice or light fried rice with minimal oil.</p>

<h2>How to Cook Himalayan Red Rice</h2>
<ol>
<li>Rinse 1 cup red rice until water runs mostly clear.</li>
<li>Soak 30–60 minutes for softer texture (optional but recommended).</li>
<li>Cook with 2.5–3 cups water — stovetop 35–40 minutes or pressure cooker 3–4 whistles.</li>
<li>Rest covered 10 minutes, then fluff gently with a fork.</li>
</ol>
<p>Red rice needs a little more water and time than white rice. The result is worth it — separate grains with a satisfying chew.</p>

<h2>Perfect Pairings from Devbhoomi Naturals</h2>
<ul>
<li><a href="{$redRice}">Pahadi Red Rice</a> — the grain at the center of every meal</li>
<li><a href="{$rajma}">Pahadi Red Rajma</a> — read our guide on <a href="{$rajmaBlog}">Pahadi Red Rajma benefits</a></li>
<li><a href="{$bhatt}">Kala Bhatt</a> — explore <a href="{$bhattBlog}">Kala Bhatt benefits</a> for another hill staple</li>
</ul>

<h2>FAQ</h2>

<h3>Why is Red Rice red?</h3>
<p>Its natural color comes from plant pigments — anthocyanins and other compounds — present in the bran layer of the grain. No artificial coloring is involved; the hue is nature's sign that the grain is still whole.</p>

<h3>Can Red Rice be eaten daily?</h3>
<p>It can be enjoyed regularly as part of a balanced diet. Many Himalayan households eat red rice every day. Combine it with dal, vegetables, and healthy fats for complete meals. People with specific medical conditions should follow their doctor's dietary advice.</p>

<h3>Does Red Rice take longer to cook than white rice?</h3>
<p>Yes. Soaking reduces cooking time and improves texture. A pressure cooker is the fastest method for busy weeknights.</p>

<h3>How should I store Red Rice?</h3>
<p>Keep in an airtight container in a cool, dry place away from direct sunlight. Use within 8–12 months for best flavor and freshness.</p>

<h2>Conclusion</h2>
<p>Red Rice is a nutritious and flavorful grain that brings traditional Himalayan goodness to modern kitchens. One swap from polished white rice opens the door to more fiber, more minerals, and meals that actually taste like something grown in real soil.</p>
<p><strong>Try Pahadi Red Rice today.</strong> Order <a href="{$redRice}">Himalayan Red Rice</a> from Devbhoomi Naturals. Pair it with <a href="{$rajma}">Pahadi Red Rajma</a> or <a href="{$bhatt}">Kala Bhatt</a>, and explore our full <a href="{$himalayan}">Uttarakhand food products</a> range. <strong>Free delivery on prepaid orders above ₹499.</strong> <a href="{$contact}">Contact us</a> at +91 9217732670.</p>
HTML;

        BlogPost::create([
            'title' => 'Why Himalayan Red Rice is a Better Choice for Healthy Eating',
            'slug' => 'himalayan-red-rice-benefits',
            'meta_title' => 'Red Rice Benefits | Himalayan Traditional Rice',
            'meta_description' => 'Explore the nutritional advantages of Himalayan Red Rice and why it is becoming a preferred alternative to regular rice.',
            'meta_keywords' => 'Himalayan Red Rice Benefits, red rice nutrition, pahadi red rice Uttarakhand, healthy rice alternative',
            'excerpt' => 'Himalayan Red Rice offers fiber, iron, and natural antioxidants with a rich nutty flavor. Discover why it beats white rice and how to cook it at home.',
            'body' => $body,
            'image' => $imagePath,
            'published_at' => now(),
            'is_published' => true,
            'sort_order' => 0,
        ]);

        Cache::forget('home.blog_posts');

        $this->command?->info('Blog post created: himalayan-red-rice-benefits');
    }
}
