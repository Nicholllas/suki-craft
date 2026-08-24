<?php

use App\Models\CartItemGroup;
use App\Models\OrderItemGroup;
use App\Models\ProductBouquetSize;
use Illuminate\Support\Facades\Blade;

test('bouquet size labels render the selected code for cart and order items', function () {
    $bouquetSize = new ProductBouquetSize(['code' => 'XL']);
    $cartItem = new CartItemGroup(['bouquet_size_label' => 'Extra Large']);
    $orderItem = new OrderItemGroup(['bouquet_size_label' => 'Extra Large']);
    $cartItem->setRelation('bouquetSize', $bouquetSize);
    $orderItem->setRelation('bouquetSize', $bouquetSize);

    $renderLabel = fn (CartItemGroup|OrderItemGroup $item): string => trim(Blade::render('<x-bouquet-size-label :item="$item" />', ['item' => $item]));

    expect($renderLabel($cartItem))->toBe('&nbsp;· XL')
        ->and($renderLabel($orderItem))->toBe('&nbsp;· XL');
});

test('bouquet size labels fall back to the stored label for historical items', function () {
    $item = new OrderItemGroup(['bouquet_size_label' => 'Custom']);

    expect(trim(Blade::render('<x-bouquet-size-label :item="$item" />', ['item' => $item])))->toBe('&nbsp;· Custom');
});
