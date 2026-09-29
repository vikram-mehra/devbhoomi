<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class JhangoraMilletBlogSeeder extends Seeder
{
    public function run(): void
    {
        if (BlogPost::where('slug', 'jhangora-millet-benefits')->exists()) {
            $this->command?->info('Blog post already exists: jhangora-millet-benefits');

            return;
        }

        $jhangora = url('/product/jhongra-barnyard-millet-mjyt');
        $redRice = url('/product/pahadi-red-rice-ym0f');
        $gahat = url('/product/pahadi-gahat-dal-bnwm');
        $millets = url('/menu/millets');
        $himalayan = url('/menu/uttarakhand-food-products');
        $contact = url('/contact-us');

        $imagePath = 'blog/jhangora-millet-benefits.png';
        if (! is_file(public_path('storage/'.$imagePath))) {
            $imagePath = null;
        }

        $body = <<<HTML
<p>Jhangora, also known as Barnyard Millet, has been cultivated in the Himalayan region for centuries. Today, health-conscious consumers are rediscovering this nutritious ancient grain — not as a fad, but as a return to what hill communities never stopped eating.</p>
<p>Light, versatile, and naturally gluten-free, <a href="{$jhangora}">Jhangora (Barnyard Millet)</a> from Devbhoomi Naturals brings authentic Uttarakhand nutrition to modern kitchens across India.</p>

<h2>What Makes Jhangora Unique?</h2>
<p>Jhangora is naturally light, nutritious, and versatile. The grains are tiny — almost like mustard seeds — but they cook into a fluffy, separate texture that works as a rice substitute, porridge base, or dessert ingredient.</p>
<p>Unlike polished white rice, jhangora retains its bran and germ. That means more fiber, more minerals, and slower energy release with every meal.</p>

<h2>Nutritional Benefits</h2>
<p>Jhangora delivers a balanced nutritional package:</p>
<ul>
<li><strong>Fiber</strong> — supports digestion and satiety</li>
<li><strong>Minerals</strong> — including iron, phosphorus, and B vitamins</li>
<li><strong>Plant nutrients</strong> — antioxidants from the whole grain</li>
<li><strong>Complex carbohydrates</strong> — steady energy without sharp spikes</li>
</ul>
<p>For families reducing refined grains, jhangora offers a practical swap — not a compromise on taste.</p>

<h2>Traditional Importance</h2>
<p>It has been a staple food in Uttarakhand households for generations. Jhangora appears during fasts, festivals, and recovery meals when the stomach needs something gentle. Hill mothers cook jhangora khichdi for children; elders prepare jhangora kheer for celebrations.</p>
<p>This grain survived in the mountains when industrial agriculture promoted wheat and polished rice everywhere else. That persistence is a sign of real nutritional value, not nostalgia alone.</p>

<h2>Why Jhangora is Popular Today</h2>

<h3>Alternative to Refined Grains</h3>
<p>Many people are replacing heavily processed foods with traditional grains. Jhangora fits the shift toward whole foods without requiring exotic imports or expensive superfood labels.</p>

<h3>Easy to Cook</h3>
<p>Jhangora can be prepared in:</p>
<ul>
<li><strong>Porridge</strong> — sweet or savory breakfast</li>
<li><strong>Khichdi</strong> — with moong dal and vegetables</li>
<li><strong>Pulao</strong> — seasoned like rice pulao</li>
<li><strong>Desserts</strong> — especially the famous Jhangora Kheer</li>
</ul>
<p>Most recipes need only rinsing, brief soaking, and 15–20 minutes of cooking — simpler than many people expect.</p>

<h3>Suitable for Healthy Lifestyles</h3>
<p>Its nutritional profile makes it attractive to health-conscious consumers. Fitness enthusiasts use jhangora as a complex carb source. Diabetic meal planners (with medical guidance) appreciate its lower glycemic impact compared to white rice. Gluten-free households rely on it as a safe grain option.</p>

<h2>Traditional Jhangora Kheer</h2>
<p>One of the most loved desserts from Uttarakhand is <strong>Jhangora Kheer</strong>, known for its rich taste and wholesome ingredients. Cooked jhangora simmers in milk with jaggery, cardamom, and garnishes of cashews and raisins until creamy. The result is a dessert that feels indulgent but is built on whole grain — the kind of sweet that grandmothers approved without hesitation.</p>
<p>Serve kheer warm during winter festivals or chilled in summer. Either way, it showcases why jhangora belongs in every pahadi pantry.</p>

<h2>Jhangora vs Rice vs Madua</h2>
<ul>
<li><strong>Jhangora</strong> — lightest, easiest digestion, best for khichdi and kheer</li>
<li><strong>Pahadi Red Rice</strong> — everyday staple with more chew; see our <a href="{$redRice}">Pahadi Red Rice</a></li>
<li><strong>Madua (Ragi)</strong> — denser, calcium-rich, ideal for rotis</li>
</ul>
<p>Rotate all three across the week for grain diversity. Browse our <a href="{$millets}">Millets collection</a> for Madua and more.</p>

<h2>How to Cook Jhangora Millet</h2>
<ol>
<li>Rinse 1 cup jhangora until water runs clear.</li>
<li>Optional: soak 30 minutes for fluffier texture.</li>
<li>Cook with 2.5 cups water — stovetop 15–18 min or pressure cooker 2 whistles.</li>
<li>Rest covered 5 minutes, then fluff with a fork.</li>
</ol>
<p>Pair with <a href="{$gahat}">Gahath Dal</a> curry for a complete traditional pahadi lunch.</p>

<h2>FAQ</h2>

<h3>Is Jhangora easy to digest?</h3>
<p>Many people find millet-based meals light and satisfying. Its small grain size and fiber profile make it gentler than heavy wheat meals — especially when cooked soft in khichdi or kheer.</p>

<h3>Can Jhangora replace rice?</h3>
<p>Yes, it can be used in many rice-based recipes. Use the same water ratio adjustments as brown rice. Expect a slightly nuttier flavor and lighter texture.</p>

<h3>Is Jhangora gluten-free?</h3>
<p>Yes. Barnyard millet is naturally gluten-free and safe for celiac diets when stored and cooked without cross-contamination.</p>

<h3>How should I store Jhangora?</h3>
<p>Keep in an airtight container in a cool, dry place. Use within 6–9 months for best freshness.</p>

<h2>Conclusion</h2>
<p>Jhangora is an ancient Himalayan grain that offers nutrition, versatility, and traditional goodness for modern lifestyles. Whether you start with a simple khichdi or the classic kheer, one meal is enough to understand why this grain is making a well-deserved comeback.</p>
<p><strong>Bring Jhangora home today.</strong> Order <a href="{$jhangora}">Jhangora (Barnyard Millet)</a> from Devbhoomi Naturals. Explore <a href="{$redRice}">Pahadi Red Rice</a>, <a href="{$gahat}">Gahath Dal</a>, and our full <a href="{$himalayan}">Uttarakhand food products</a> range. <strong>Free delivery on prepaid orders above ₹499.</strong> <a href="{$contact}">Contact us</a> at +91 9217732670.</p>
HTML;

        BlogPost::create([
            'title' => 'Jhangora Millet: The Ancient Grain Making a Modern Comeback',
            'slug' => 'jhangora-millet-benefits',
            'meta_title' => 'Jhangora Benefits | Barnyard Millet Nutrition',
            'meta_description' => 'Discover why Jhangora millet is becoming a preferred healthy grain for modern diets and traditional Himalayan meals.',
            'meta_keywords' => 'Jhangora Millet Benefits, barnyard millet nutrition, Jhangora kheer, Himalayan millet Uttarakhand',
            'excerpt' => 'Jhangora (barnyard millet) is Uttarakhand’s ancient super-grain — light, gluten-free, and perfect for khichdi, pulao, and traditional kheer. Discover benefits and recipes.',
            'body' => $body,
            'image' => $imagePath,
            'published_at' => now(),
            'is_published' => true,
            'sort_order' => 0,
        ]);

        Cache::forget('home.blog_posts');

        $this->command?->info('Blog post created: jhangora-millet-benefits');
    }
}
