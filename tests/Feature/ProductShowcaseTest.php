<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductShowcaseTest extends TestCase
{
    public function test_showcase_renders_static_product_story_without_commerce_data(): void
    {
        $this->get('/showcase/jhangora-millet')
            ->assertOk()
            ->assertSee('Jhangora (Barnyard Millet)')
            ->assertSee('How to cook Jhangora (Barnyard Millet)')
            ->assertSee('Wash 1 cup of Jhangora thoroughly')
            ->assertSee('Jhangora, barnyard millet, Jhangora millet Uttarakhand')
            ->assertSee('images.unsplash.com')
            ->assertSee('Article')
            ->assertDontSee('Other Products')
            ->assertDontSee('showcase-price');
    }

    public function test_each_configured_showcase_slug_is_available(): void
    {
        foreach (array_keys(config('showcase')) as $slug) {
            $this->get('/showcase/'.$slug)
                ->assertOk()
                ->assertSee(config('showcase.'.$slug.'.title'));
        }
    }

    public function test_jhangora_showcase_uses_the_attached_local_image(): void
    {
        $this->get('/showcase/jhangora-millet')
            ->assertOk()
            ->assertSee('/images/jhangora-attached.jpg')
            ->assertSee('Jhangora ki Kheer');
    }

    public function test_unknown_showcase_slug_returns_not_found(): void
    {
        $this->get('/showcase/does-not-exist')->assertNotFound();
    }
}
