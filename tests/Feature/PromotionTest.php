<?php

use App\Models\PromoBanner;

test('storefront only receives active banners in display order', function () {
    $hiddenBanner = PromoBanner::factory()->create(['is_active' => false, 'sort_order' => 0]);
    $secondBanner = PromoBanner::factory()->create(['image_path' => 'promo-banners/banner-kedua.jpg', 'sort_order' => 2, 'title' => 'Banner kedua']);
    $firstBanner = PromoBanner::factory()->create(['image_path' => 'promo-banners/banner-pertama.jpg', 'sort_order' => 1, 'title' => 'Banner pertama']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSeeInOrder([$firstBanner->title, $secondBanner->title])
        ->assertDontSee($hiddenBanner->title)
        ->assertSee('/storage/promo-banners/banner-pertama.jpg', false)
        ->assertSee('object-contain', false)
        ->assertSee('aria-label="Banner sebelumnya"', false)
        ->assertSee('aria-label="Banner berikutnya"', false);
});
