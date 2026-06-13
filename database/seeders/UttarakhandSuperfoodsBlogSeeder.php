<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class UttarakhandSuperfoodsBlogSeeder extends Seeder
{
    public function run(): void
    {
        if (BlogPost::where('slug', 'uttarakhand-superfoods')->exists()) {
            $this->command?->info('Blog post already exists: uttarakhand-superfoods');

            return;
        }

        $allProducts = url('/menu/uttarakhand-food-products');
        $rajma = url('/product/pahadi-rajma-kn1e');
        $bhatt = url('/product/pahadi-bhatt-biui');
        $gahat = url('/product/pahadi-gahat-dal-bnwm');
        $jhangora = url('/product/jhongra-barnyard-millet-mjyt');
        $redRice = url('/product/pahadi-red-rice-ym0f');
        $rajmaBlog = url('/blog/pahadi-red-rajma-benefits');
        $bhattBlog = url('/blog/kala-bhatt-benefits');
        $gahatBlog = url('/blog/gahath-dal-benefits');
        $jhangoraBlog = url('/blog/jhangora-millet-benefits');
        $redRiceBlog = url('/blog/himalayan-red-rice-benefits');
        $pulses = url('/menu/pahadi-pulses');
        $millets = url('/menu/millets');
        $contact = url('/contact-us');

        $imagePath = 'blog/uttarakhand-superfoods.png';
        if (! is_file(public_path('storage/'.$imagePath))) {
            $imagePath = null;
        }

        $body = <<<HTML
<p>The Himalayan state of Uttarakhand is home to some of India's most nutritious traditional foods. These ingredients have nourished mountain communities for generations and are now attracting attention from health-conscious consumers worldwide — not because of marketing hype, but because they deliver real nutrition from real soil.</p>
<p>From protein-rich pulses to ancient millets and whole-grain rice, Uttarakhand's pantry offers everything needed for balanced, flavorful meals. Browse our full <a href="{$allProducts}">Uttarakhand food products</a> collection at Devbhoomi Naturals and bring hill-country goodness to your kitchen.</p>

<h2>5 Traditional Uttarakhand Superfoods</h2>

<h3>1. Pahadi Red Rajma</h3>
<p>Known for its rich taste, soft texture, and nutritional value, <a href="{$rajma}">Pahadi Red Rajma</a> is the crown jewel of hill pulses. Grown slowly in Uttarakhand's mineral-rich soil, it cooks into a creamy, deeply flavored curry that pairs perfectly with rice or millet. High in plant protein, fiber, and iron, rajma chawal is not just comfort food — it is how mountain families have stayed strong through long winters.</p>
<p><a href="{$rajmaBlog}">Read more: Pahadi Red Rajma benefits →</a></p>

<h3>2. Kala Bhatt</h3>
<p>A protein-rich black soybean used in traditional Kumaoni cuisine, <a href="{$bhatt}">Kala Bhatt (Pahadi Bhatt)</a> is one of Uttarakhand's best-kept secrets. Unlike ordinary soybeans, hill-grown bhatt develops a distinctive earthy flavor that shines in simple curries with jamboo (a local herb) and garlic. It is a staple for vegetarians seeking dense, satisfying protein without processed supplements.</p>
<p><a href="{$bhattBlog}">Read more: Kala Bhatt benefits →</a></p>

<h3>3. Gahath Dal</h3>
<p>A nutrient-dense pulse that has been part of Himalayan diets for centuries, <a href="{$gahat}">Gahath Dal (Horse Gram)</a> is prized for its warming properties and high protein content. Hill communities traditionally serve gahath during cold months and recovery periods. Cook it as a thick dal with garlic and cumin, or add to soups — either way, it delivers serious nutrition in every spoonful.</p>
<p><a href="{$gahatBlog}">Read more: Gahath Dal benefits →</a></p>

<h3>4. Jhangora</h3>
<p>An ancient millet celebrated for its versatility and nutrition, <a href="{$jhangora}">Jhangora (Barnyard Millet)</a> is Uttarakhand's answer to refined grains. Naturally gluten-free and light on digestion, jhangora works as porridge, khichdi, pulao, and the beloved Jhangora Kheer. Its tiny grains pack fiber, minerals, and complex carbohydrates — ideal for families reducing white rice and wheat.</p>
<p><a href="{$jhangoraBlog}">Read more: Jhangora Millet benefits →</a></p>

<h3>5. Himalayan Red Rice</h3>
<p>A traditional grain valued for its flavor and wholesome nature, <a href="{$redRice}">Pahadi Red Rice</a> retains its bran layer for more fiber, iron, and antioxidants than polished white rice. The nutty, slightly chewy texture makes every dal-rice meal more satisfying. Red rice has been the everyday staple in hill households long before "whole grain" became a health trend.</p>
<p><a href="{$redRiceBlog}">Read more: Himalayan Red Rice benefits →</a></p>

<h2>Why Himalayan Foods Are Gaining Popularity</h2>

<h3>Traditional Farming Practices</h3>
<p>Many Himalayan crops are cultivated using time-tested agricultural methods. Smallholder farmers in Uttarakhand often rely on rain-fed terraces, crop rotation, and minimal chemical inputs. That slower, more natural growing cycle produces grains and pulses with denser nutrition and more authentic flavor than mass-farmed alternatives.</p>

<h3>Nutrient-Dense Foods</h3>
<p>Mountain-grown crops often become an important part of balanced diets. Cooler temperatures, clean air, and mineral-rich soil create conditions where plants develop robust nutrient profiles. When you eat pahadi rajma instead of generic kidney beans, or jhangora instead of polished rice, you are choosing food that worked harder to nourish itself — and you.</p>

<h3>Authentic Taste</h3>
<p>The flavor of traditional Himalayan ingredients is difficult to replicate. Hill rajma tastes different from plains rajma. Red rice has a depth that white rice cannot match. Kala bhatt carries an earthiness born of Kumaoni terroir. These are not interchangeable commodities — they are regional foods with character.</p>

<h2>Building a Healthy Himalayan Meal</h2>
<p>A nutritious meal can include:</p>
<ul>
<li><strong>Red Rice or Jhangora</strong> — as your complex carbohydrate base (<a href="{$redRice}">Red Rice</a> | <a href="{$jhangora}">Jhangora</a>)</li>
<li><strong>Gahath Dal or Rajma</strong> — for plant protein and fiber (<a href="{$gahat}">Gahath Dal</a> | <a href="{$rajma}">Rajma</a>)</li>
<li><strong>Fresh vegetables</strong> — seasonal greens, pumpkin, or radish from local markets</li>
<li><strong>Traditional spices</strong> — cumin, coriander, jamboo, and garlic for flavor without excess oil</li>
</ul>

<h3>Sample Weekly Meal Plan</h3>
<ul>
<li><strong>Monday:</strong> Red rice + Pahadi Red Rajma curry</li>
<li><strong>Tuesday:</strong> Jhangora khichdi with vegetables</li>
<li><strong>Wednesday:</strong> Kala Bhatt curry + roti</li>
<li><strong>Thursday:</strong> Gahath dal + red rice</li>
<li><strong>Friday:</strong> Jhangora pulao with seasonal sabzi</li>
<li><strong>Weekend:</strong> Jhangora Kheer or rajma chawal — hill-style comfort food</li>
</ul>
<p>Rotate grains and pulses across the week for variety and complete nutrition. Explore our <a href="{$pulses}">Pahadi Pulses</a> and <a href="{$millets}">Millets</a> collections to stock your pantry.</p>

<h2>FAQ</h2>

<h3>Why are Uttarakhand foods considered special?</h3>
<p>Their traditional cultivation methods and unique mountain environment contribute to their distinct qualities. Shorter growing seasons, terraced fields, and generations of seed selection have created varieties — like pahadi rajma and kala bhatt — that thrive nowhere else quite the same way.</p>

<h3>Which superfood should beginners try first?</h3>
<p>Pahadi Red Rajma and Jhangora are excellent starting points. Rajma is familiar to most Indian households — just swap in the pahadi variety for noticeably better taste. Jhangora cooks like rice, works in khichdi, and makes a memorable kheer dessert. Both are forgiving for first-time cooks.</p>

<h3>Are Uttarakhand superfoods suitable for vegetarian diets?</h3>
<p>Absolutely. Rajma, gahath, kala bhatt, jhangora, and red rice form a complete vegetarian nutrition system — protein from pulses, complex carbs from grains and millets, fiber and minerals from whole foods. No animal products required.</p>

<h3>Where can I buy authentic Uttarakhand superfoods?</h3>
<p>Devbhoomi Naturals sources directly from hill farmers and delivers across India. Browse our <a href="{$allProducts}">full product range</a> — rajma, bhatt, gahath, jhangora, red rice, and more.</p>

<h2>Conclusion</h2>
<p>Traditional Uttarakhand foods offer a perfect blend of heritage, nutrition, and authentic taste. By incorporating these ingredients into your daily meals, you can enjoy the goodness of Himalayan agriculture while supporting sustainable and traditional farming communities.</p>
<p><strong>Start your Himalayan food journey today.</strong> Shop <a href="{$allProducts}">all Uttarakhand superfoods</a> at Devbhoomi Naturals — <a href="{$rajma}">Pahadi Red Rajma</a>, <a href="{$bhatt}">Kala Bhatt</a>, <a href="{$gahat}">Gahath Dal</a>, <a href="{$jhangora}">Jhangora</a>, and <a href="{$redRice}">Red Rice</a>. <strong>Free delivery on prepaid orders above ₹499.</strong> <a href="{$contact}">Contact us</a> at +91 9217732670.</p>
HTML;

        BlogPost::create([
            'title' => 'Traditional Uttarakhand Superfoods for a Healthier Lifestyle',
            'slug' => 'uttarakhand-superfoods',
            'meta_title' => 'Uttarakhand Superfoods | Himalayan Healthy Foods',
            'meta_description' => 'Discover traditional Uttarakhand superfoods including Rajma, Kala Bhatt, Gahath Dal, Jhangora, and Red Rice for healthy living.',
            'meta_keywords' => 'Uttarakhand Superfoods, Himalayan healthy foods, pahadi rajma, kala bhatt, gahath dal, jhangora millet, red rice',
            'excerpt' => 'Explore 5 traditional Uttarakhand superfoods — Rajma, Kala Bhatt, Gahath Dal, Jhangora, and Red Rice — and learn how to build healthy Himalayan meals at home.',
            'body' => $body,
            'image' => $imagePath,
            'published_at' => now(),
            'is_published' => true,
            'sort_order' => 0,
        ]);

        Cache::forget('home.blog_posts');

        $this->command?->info('Blog post created: uttarakhand-superfoods');
    }
}
