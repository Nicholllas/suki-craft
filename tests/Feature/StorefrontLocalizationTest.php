<?php

test('English locale translates the catalogue, footer, and ordering guide', function () {
    $this->withSession(['locale' => 'en'])
        ->get(route('products.index'))
        ->assertSuccessful()
        ->assertSee('Flowers for every message you want to share.')
        ->assertSee('Explore')
        ->assertSee('No bouquets found');

    $this->withSession(['locale' => 'en'])
        ->get(route('how_to_order'))
        ->assertSuccessful()
        ->assertSee('From choosing a bouquet to reaching its recipient.')
        ->assertSee('Frequently asked questions');
});
