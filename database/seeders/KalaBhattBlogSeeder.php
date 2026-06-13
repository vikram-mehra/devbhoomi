<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class KalaBhattBlogSeeder extends Seeder
{
    public function run(): void
    {
        if (BlogPost::where('slug', 'kala-bhatt-benefits')->exists()) {
            $this->command?->info('Blog post already exists: kala-bhatt-benefits');

            return;
        }

        $bhatt = url('/product/pahadi-bhatt-biui');
        $rajma = url('/product/pahadi-rajma-kn1e');
        $jhangora = url('/product/jhongra-barnyard-millet-mjyt');
        $pulses = url('/menu/pahadi-pulses');
        $himalayan = url('/menu/uttarakhand-food-products');
        $contact = url('/contact-us');

        $imagePath = 'blog/kala-bhatt-benefits.png';
        if (! is_file(public_path('storage/'.$imagePath))) {
            $imagePath = null;
        }

        $body = <<<HTML
<p>Kala Bhatt, also known as black soybean, is one of Uttarakhand's most treasured traditional foods. For centuries, people living in the Himalayan region have included Kala Bhatt in their daily meals because of its rich nutrition, earthy flavor, and versatility.</p>
<p>Unlike ordinary soybeans, Kala Bhatt has a distinct taste and is widely used in traditional mountain recipes such as <strong>Kala Bhatt Ki Churkani</strong> — a dish that defines Kumaoni home cooking as much as rajma or gahat dal.</p>

<h2>What is Kala Bhatt?</h2>
<p>Kala Bhatt is a black-colored soybean variety cultivated in the Himalayan regions of Uttarakhand. Also called <strong>Pahadi Bhatt</strong>, it is valued for:</p>
<ul>
<li>High protein content</li>
<li>Rich dietary fiber</li>
<li>Traditional medicinal importance</li>
<li>Excellent earthy taste</li>
<li>Natural cultivation without heavy chemical inputs</li>
</ul>
<p>At Devbhoomi Naturals, our <a href="{$bhatt}">Pahadi Bhatt (Kala Bhatt)</a> is sourced from hill farmers who have grown this crop for generations — preserving both seed quality and authentic flavor.</p>

<h2>Nutritional Profile of Kala Bhatt</h2>
<p>Kala Bhatt contains:</p>
<ul>
<li>Plant protein</li>
<li>Iron</li>
<li>Calcium</li>
<li>Magnesium</li>
<li>Potassium</li>
<li>Antioxidants</li>
<li>Dietary fiber</li>
</ul>
<p>This combination makes it one of the most nutrient-dense pulses in the Himalayan diet — especially valuable for vegetarians who need complete protein from plant sources.</p>

<h2>Top Benefits of Kala Bhatt</h2>

<h3>Supports Muscle Growth</h3>
<p>Protein is essential for maintaining muscles and supporting recovery. Kala Bhatt delivers more protein per serving than many common dals, making it ideal after physical work, exercise, or for growing children in vegetarian households.</p>

<h3>Rich in Antioxidants</h3>
<p>The dark outer layer contains beneficial plant compounds that help protect cells. Black soybeans derive their color from anthocyanins — the same antioxidant family found in blueberries and dark grapes.</p>

<h3>Helps Maintain Energy Levels</h3>
<p>Its balanced nutrient profile provides sustained energy. Unlike quick-burning refined snacks, a meal with Kala Bhatt releases energy gradually through complex carbohydrates and protein working together.</p>

<h3>Supports Healthy Digestion</h3>
<p>Fiber contributes to digestive wellness and gut health. Soaked and well-cooked bhatt is easier on the stomach than undercooked legumes — always soak overnight and pressure cook thoroughly.</p>

<h3>Heart-Friendly Food Choice</h3>
<p>Traditional diets often include Kala Bhatt as a wholesome source of plant-based nutrition. Low in saturated fat and high in fiber, it supports heart-conscious eating when paired with vegetables and whole grains like <a href="{$jhangora}">Jhangora (Barnyard Millet)</a>.</p>

<h3>Supports Bone Health</h3>
<p>Calcium and magnesium in Kala Bhatt contribute to bone maintenance — particularly important for women and older adults on plant-based diets.</p>

<h3>Traditional Ayurvedic Value</h3>
<p>Hill communities have long regarded bhatt as a strengthening food for winter months. Its warming nature and dense nutrition make it a seasonal staple from October through March.</p>

<h2>Traditional Kala Bhatt Recipes</h2>

<h3>Kala Bhatt Ki Churkani</h3>
<p>A famous Kumaoni dish prepared with roasted black soybean and traditional spices. The bhatt is dry-roasted, ground coarsely, and cooked with jamboo (Himalayan thyme), garlic, and local masalas. Served with steamed rice, it is comfort food at its most nutritious.</p>

<h3>Kala Bhatt Curry</h3>
<p>A nutritious curry enjoyed with rice or millet. Soak bhatt overnight, pressure cook until soft, then simmer in an onion-tomato masala with cumin and coriander. Pairs beautifully with <a href="{$rajma}">Pahadi Red Rajma</a> on alternate days for protein variety.</p>

<h3>Bhatt with Jhangora Khichdi</h3>
<p>For a complete pahadi meal, serve bhatt curry alongside <a href="{$jhangora}">Jhangora millet khichdi</a> — protein, fiber, and slow carbohydrates in one plate.</p>

<h2>Kala Bhatt vs Regular Soybean</h2>
<p>Regular yellow soybeans are widely used in commercial products. Kala Bhatt differs in:</p>
<ul>
<li><strong>Flavor</strong> — earthier, more intense, suited to traditional recipes</li>
<li><strong>Origin</strong> — mountain-grown in Uttarakhand vs plain-region farming</li>
<li><strong>Culinary use</strong> — whole pulse cooking vs industrial processing</li>
<li><strong>Heritage</strong> — central to Kumaoni/Garhwali cuisine for centuries</li>
</ul>

<h2>How to Cook Kala Bhatt</h2>
<ol>
<li>Rinse and soak 1 cup bhatt in water for 8–10 hours.</li>
<li>Pressure cook with fresh water, salt, and turmeric (6–7 whistles).</li>
<li>Temper ghee with cumin, garlic, and jamboo if available.</li>
<li>Simmer with spices until creamy. Serve hot.</li>
</ol>
<p>Order authentic <a href="{$bhatt}">Pahadi Bhatt (Kala Bhatt)</a> from our <a href="{$pulses}">Pahadi-Pulses</a> collection.</p>

<h2>FAQ</h2>

<h3>Is Kala Bhatt healthier than regular soybean?</h3>
<p>Kala Bhatt contains valuable nutrients and is widely appreciated in traditional Himalayan diets. Its antioxidant-rich dark coat and traditional mountain cultivation give it advantages over mass-produced yellow soybean for whole-food cooking.</p>

<h3>Is Kala Bhatt suitable for vegetarians?</h3>
<p>Yes, it is an excellent plant-based protein source — one of the highest among Himalayan pulses. Ideal for vegetarians, vegans, and anyone reducing meat consumption.</p>

<h3>How should Kala Bhatt be stored?</h3>
<p>Keep in an airtight container in a cool, dry place away from sunlight. Use within 8–12 months for best results. Avoid moisture — bhatt absorbs humidity quickly.</p>

<h3>Can diabetics eat Kala Bhatt?</h3>
<p>Its fiber and protein support balanced meals, but portion control matters. Consult your healthcare provider for personalised advice.</p>

<h2>Conclusion</h2>
<p>Kala Bhatt represents the wisdom of traditional Himalayan nutrition. Rich in protein and authentic flavor, it is a wonderful addition to a healthy lifestyle — whether you cook classic Churkani or a simple weeknight curry.</p>
<p><strong>Try authentic Kala Bhatt today.</strong> Shop <a href="{$bhatt}">Pahadi Bhatt (Black Soybean)</a> at Devbhoomi Naturals. Explore <a href="{$himalayan}">Uttarakhand food products</a>, pair with <a href="{$rajma}">Pahadi Rajma</a> and <a href="{$jhangora}">Jhangora Millet</a>, and enjoy <strong>free delivery on prepaid orders above ₹499</strong>. <a href="{$contact}">Contact us</a> at +91 9217732670 for bulk orders.</p>
HTML;

        BlogPost::create([
            'title' => 'Kala Bhatt: Uttarakhand’s Traditional Superfood Packed with Protein',
            'slug' => 'kala-bhatt-benefits',
            'meta_title' => 'Kala Bhatt Benefits | Himalayan Black Soybean',
            'meta_description' => 'Discover the nutritional benefits of Kala Bhatt, the traditional black soybean of Uttarakhand known for protein, fiber, and authentic Himalayan nutrition.',
            'meta_keywords' => 'Kala Bhatt Benefits, Himalayan black soybean, Pahadi Bhatt, Uttarakhand superfood',
            'excerpt' => 'Kala Bhatt (black soybean) is Uttarakhand’s protein-rich superfood. Learn its benefits, traditional recipes like Churkani, and why Pahadi Bhatt beats regular soybean.',
            'body' => $body,
            'image' => $imagePath,
            'published_at' => now(),
            'is_published' => true,
            'sort_order' => 0,
        ]);

        Cache::forget('home.blog_posts');

        $this->command?->info('Blog post created: kala-bhatt-benefits');
    }
}
