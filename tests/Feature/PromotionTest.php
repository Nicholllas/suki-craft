<?php

use App\Models\PromoBanner;

test('storefront only receives active banners in display order', function () {
    $hiddenBanner = PromoBanner::factory()->create(['is_active' => false, 'sort_order' => 0]);
    $secondBanner = PromoBanner::factory()->create(['sort_order' => 2, 'title' => 'Banner kedua']);
    $firstBanner = PromoBanner::factory()->create(['sort_order' => 1, 'title' => 'Banner pertama']);

    $this->get(route('home'))->assertOk()->assertSeeInOrder([$firstBanner->title, $secondBanner->title])->assertDontSee($hiddenBanner->title);
});
