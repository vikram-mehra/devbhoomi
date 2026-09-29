<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class PahadiRedRajmaBlogSeeder extends Seeder
{
    public function run(): void
    {
        if (BlogPost::where('slug', 'pahadi-red-rajma-benefits')->exists()) {
            $this->command?->info('Blog post already exists: pahadi-red-rajma-benefits');

            return;
        }

        $rajma = url('/product/pahadi-rajma-kn1e');
        $bhatt = url('/product/pahadi-bhatt-biui');
        $gahat = url('/product/pahadi-gahat-dal-bnwm');
        $pulses = url('/menu/pahadi-pulses');
        $himalayan = url('/menu/uttarakhand-food-products');
        $contact = url('/contact-us');

        $imagePath = 'blog/pahadi-red-rajma-benefits.png';
        if (! is_file(public_path('storage/'.$imagePath))) {
            $imagePath = null;
        }

        $body = <<<HTML
<p>Pahadi Red Rajma is more than just a traditional ingredient from the Himalayan region. Grown naturally in the fertile hills of Uttarakhand, this premium variety of rajma is known for its rich taste, soft texture, and impressive nutritional profile.</p>
<p>Unlike ordinary kidney beans available in the market, Himalayan Red Rajma develops slowly in the mountain climate, allowing it to absorb valuable nutrients from the soil. This results in a naturally flavorful and wholesome food that has been a part of traditional mountain diets for generations.</p>

<h2>What Makes Pahadi Red Rajma Special?</h2>
<p>The unique geographical conditions of the Himalayas contribute to the superior quality of Pahadi Red Rajma.</p>

<h3>Naturally Grown in Mountain Regions</h3>
<p>The cool climate and mineral-rich soil help produce rajma with:</p>
<ul>
<li>Better taste</li>
<li>Softer texture</li>
<li>Higher nutrient density</li>
<li>Natural farming practices</li>
</ul>

<h3>Rich Traditional Heritage</h3>
<p>For generations, mountain communities have relied on rajma as a staple source of nutrition and energy. A bowl of rajma chawal after a long day in the fields is not just comfort food — it is how hill families have stayed nourished through harsh winters.</p>

<h2>Nutritional Value of Pahadi Red Rajma</h2>
<p>Pahadi Red Rajma contains:</p>
<ul>
<li>Plant-based protein</li>
<li>Dietary fiber</li>
<li>Iron</li>
<li>Magnesium</li>
<li>Potassium</li>
<li>Folate</li>
<li>Complex carbohydrates</li>
</ul>
<p>These nutrients make it an excellent addition to a balanced diet — especially for vegetarians, active families, and anyone replacing processed foods with whole ingredients.</p>

<h2>10 Health Benefits of Pahadi Red Rajma</h2>

<h3>1. Excellent Source of Plant Protein</h3>
<p>Rajma is highly valued among vegetarians because it provides substantial protein without animal products. One well-cooked serving paired with rice or millet delivers amino acids your body needs for repair and daily function.</p>

<h3>2. Supports Heart Health</h3>
<p>Its fiber and mineral content may help support healthy cholesterol levels and overall cardiovascular wellness. Soluble fiber in rajma binds with cholesterol in the digestive tract, supporting heart-friendly eating patterns.</p>

<h3>3. Helps Manage Weight</h3>
<p>The combination of protein and fiber promotes fullness and reduces unnecessary snacking. A rajma-based lunch often keeps you satisfied until dinner — without the energy crash that refined carbs cause.</p>

<h3>4. Improves Digestion</h3>
<p>Dietary fiber supports healthy digestion and gut health. Soaked and thoroughly cooked rajma is easier to digest and feeds beneficial gut bacteria.</p>

<h3>5. Supports Blood Sugar Balance</h3>
<p>Complex carbohydrates release energy gradually compared to highly processed foods. This makes rajma a smarter staple for people monitoring blood sugar — always with portion control and medical guidance where needed.</p>

<h3>6. Provides Long-Lasting Energy</h3>
<p>Traditional Himalayan communities have consumed rajma for sustained energy throughout the day. Farmers, trekkers, and students in the hills rely on it because the energy lasts — it does not spike and fade within an hour.</p>

<h3>7. Rich in Iron</h3>
<p>Iron contributes to normal oxygen transport within the body. Combined with vitamin C-rich sides like tomato-based gravy or lemon, rajma helps your body use iron more effectively.</p>

<h3>8. Supports Muscle Health</h3>
<p>Protein plays an important role in muscle maintenance and recovery. After exercise or physical labour, rajma with rice rebuilds what the body has used.</p>

<h3>9. Helps Maintain Healthy Bones</h3>
<p>Minerals such as magnesium contribute to bone health. Alongside calcium from <a href="{$himalayan}">other Himalayan staples</a>, rajma fits into a bone-supporting diet.</p>

<h3>10. Promotes Overall Wellness</h3>
<p>Regular consumption as part of a balanced diet supports general health and nutrition. It is not a miracle food — but as a weekly staple, it outperforms most packaged alternatives on every nutrition marker that matters.</p>

<h2>How to Cook Pahadi Red Rajma</h2>

<h3>Soaking Tips</h3>
<ul>
<li>Soak overnight in plenty of water.</li>
<li>Rinse thoroughly before cooking.</li>
<li>Discard soak water to reduce cooking gas and improve digestion.</li>
</ul>

<h3>Cooking Method</h3>
<ol>
<li>Pressure cook soaked rajma with salt and bay leaf until completely soft (5–6 whistles).</li>
<li>Prepare a masala base with onions, tomatoes, ginger, garlic, and spices.</li>
<li>Simmer cooked rajma in the masala for 15–20 minutes on low flame.</li>
<li>Serve with <a href="{$himalayan}">Pahadi Red Rice</a>, steamed rice, or millet.</li>
</ol>
<p>For an authentic pahadi thali, pair rajma with <a href="{$gahat}">Pahadi Gahat Dal</a> on alternate days and <a href="{$bhatt}">Pahadi Bhatt</a> for protein variety.</p>

<h2>Why Choose Organic Himalayan Rajma?</h2>
<p>When you choose authentic Himalayan Rajma from Devbhoomi Naturals, you enjoy:</p>
<ul>
<li>Better taste from slow mountain-grown crops</li>
<li>Traditional farming practices without unnecessary chemicals</li>
<li>Premium quality with transparent sourcing from Uttarakhand</li>
<li>Nutrient-rich ingredients delivered pan-India</li>
</ul>
<p>Shop our <a href="{$rajma}">Pahadi Rajma</a> directly or browse the full <a href="{$pulses}">Pahadi-Pulses collection</a> for Gahat, Bhatt, Lobia, and more.</p>

<h2>Frequently Asked Questions</h2>

<h3>Is Pahadi Red Rajma different from regular rajma?</h3>
<p>Yes. It is known for its unique flavor, softer texture when cooked, and mountain-grown quality. Market rajma is often bulk-sourced with inconsistent age and storage — pahadi rajma from Uttarakhand hills has a distinct earthy sweetness.</p>

<h3>Can it be eaten daily?</h3>
<p>It can be included regularly as part of a balanced diet. Rotate with other pulses through the week for digestive comfort and nutrient diversity.</p>

<h3>Is it suitable for vegetarians?</h3>
<p>Absolutely. It is one of the most valuable sources of plant-based protein in Indian vegetarian cooking.</p>

<h3>How should it be stored?</h3>
<p>Store dried rajma in an airtight container in a cool, dry place. Use within 8–12 months for best cooking results. Keep away from moisture and direct sunlight.</p>

<h2>Conclusion</h2>
<p>Pahadi Red Rajma is a traditional Himalayan superfood that combines taste, nutrition, and heritage. Whether you are looking for a healthy protein source, improved digestion, or wholesome meals, this mountain-grown ingredient deserves a place in your kitchen.</p>
<p><strong>Ready to cook authentic rajma chawal?</strong> Order <a href="{$rajma}">Pahadi Rajma from Devbhoomi Naturals</a> today. Explore our <a href="{$pulses}">Pahadi-Pulses</a> and <a href="{$himalayan}">Healthy Himalayan Foods</a> collections — free delivery on prepaid orders above ₹499. Questions? <a href="{$contact}">Contact us</a> at +91 9217732670.</p>
HTML;

        BlogPost::create([
            'title' => '10 Amazing Health Benefits of Pahadi Red Rajma You Should Know',
            'slug' => 'pahadi-red-rajma-benefits',
            'meta_title' => 'Pahadi Red Rajma Benefits | Organic Himalayan Rajma',
            'meta_description' => 'Discover the nutritional value and health benefits of authentic Pahadi Red Rajma from the Himalayas and why it deserves a place in your diet.',
            'meta_keywords' => 'Pahadi Red Rajma Benefits, organic Himalayan rajma, pahadi rajma health benefits, Uttarakhand rajma',
            'excerpt' => 'Discover why Pahadi Red Rajma from Uttarakhand is richer in protein, fiber, and flavor than regular kidney beans — plus 10 health benefits and cooking tips.',
            'body' => $body,
            'image' => $imagePath,
            'published_at' => now(),
            'is_published' => true,
            'sort_order' => 0,
        ]);

        Cache::forget('home.blog_posts');

        $this->command?->info('Blog post created: pahadi-red-rajma-benefits');
    }
}
