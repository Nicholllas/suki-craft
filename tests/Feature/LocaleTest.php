<?php

test('guests can choose English and keep their selection for later requests', function () {
    $this->from(route('home'))
        ->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect(route('home'))
        ->assertSessionHas('locale', 'en');

    $this->withSession(['locale' => 'en'])
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Complimentary message card with every bouquet order')
        ->assertSee('Collections')
        ->assertSee('Flowers for every')
        ->assertDontSee('Bunga untuk setiap');
});

test('guests cannot choose an unsupported language', function () {
    $this->from(route('home'))
        ->post(route('locale.update'), ['locale' => 'fr'])
        ->assertRedirect(route('home'))
        ->assertSessionHasErrors('locale');
});
