<?php

test('English About page includes the full set of storefront sections', function () {
    $this->withSession(['locale' => 'en'])
        ->get(route('about'))
        ->assertSuccessful()
        ->assertSee('What we do')
        ->assertSee('Portfolio')
        ->assertSee('Our team')
        ->assertSee('Principles and values')
        ->assertSee('Recognition')
        ->assertSee('Reviews')
        ->assertSee('Let’s begin the story');
});
