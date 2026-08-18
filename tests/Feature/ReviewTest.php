<?php

use App\Enums\AdminRole;
use App\Enums\OrderStatus;
use App\Enums\ReviewStatus;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItemGroup;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

function deliveredOrderItemGroup(?Customer $customer = null): OrderItemGroup
{
    $order = Order::factory()->create([
        'customer_id' => $customer?->id,
        'customer_name' => $customer?->name ?? 'Nadia Pelanggan',
        'status' => OrderStatus::DELIVERED,
    ]);

    return OrderItemGroup::factory()->for($order)->create();
}

test('a delivered order item group can receive one pending review with a bouquet photo', function () {
    fakeStorageDisk('public');
    $customer = Customer::factory()->create();
    $orderItem = deliveredOrderItemGroup($customer);

    $review = app(ReviewService::class)->submit($orderItem, [
        'comment' => 'Buketnya segar dan sangat cantik.',
        'photo' => UploadedFile::fake()->image('buket.jpg'),
        'rating' => 5,
    ], $customer);

    expect($review->status)->toBe(ReviewStatus::PENDING)
        ->and($review->reviewer_name)->toBe($orderItem->order->customer_name)
        ->and($review->customer_id)->toBe($customer->id)
        ->and($review->photo_path)->not->toBeNull();
    $this->assertModelExists($review);
    Storage::disk('public')->assertExists($review->photo_path);
    expect(fn () => app(ReviewService::class)->submit($orderItem, ['rating' => 4], $customer))
        ->toThrow(ValidationException::class);
});

test('a review requires a delivered order item', function () {
    $orderItem = OrderItemGroup::factory()->for(Order::factory()->state(['status' => OrderStatus::PROCESSING]))->create();

    expect(app(ReviewService::class)->canReview($orderItem))->toBeFalse()
        ->and(fn () => app(ReviewService::class)->submit($orderItem, ['rating' => 5]))->toThrow(ValidationException::class);
});

test('a guest can submit a review only after tracking the matching order', function () {
    $orderItem = deliveredOrderItemGroup();
    $order = $orderItem->order;

    $this->post(route('tracking.store'), [
        'order_number' => $order->order_number,
        'phone' => $order->customer_phone,
    ])->assertRedirect(route('tracking.show', $order));

    $this->post(route('tracking.reviews.store', [$order, $orderItem]), [
        'comment' => 'Sangat menyenangkan.',
        'rating' => 4,
    ])->assertRedirect(route('tracking.show', $order))
        ->assertSessionHas('success', 'Ulasan kamu sedang menunggu moderasi.');

    $review = Review::query()->sole();

    expect($review->customer_id)->toBeNull()
        ->and($review->order_item_group_id)->toBe($orderItem->id);
});

test('a customer cannot submit a review for another customers order', function () {
    $orderItem = deliveredOrderItemGroup(Customer::factory()->create());
    $customer = Customer::factory()->create();

    $this->actingAs($customer, 'customer')
        ->post(route('customer.orders.reviews.store', [$orderItem->order, $orderItem]), ['rating' => 5])
        ->assertNotFound();
});

test('an admin can approve or reject pending reviews and the storefront only shows approved reviews', function () {
    $admin = Admin::create([
        'email' => 'admin@example.com',
        'is_active' => true,
        'name' => 'Admin Suki',
        'password' => 'password',
        'role' => AdminRole::ADMIN,
    ]);
    $approvedItem = deliveredOrderItemGroup();
    $rejectedItem = deliveredOrderItemGroup();
    $reviewService = app(ReviewService::class);
    $approvedReview = $reviewService->submit($approvedItem, ['comment' => 'Sangat indah.', 'rating' => 5]);
    $rejectedReview = $reviewService->submit($rejectedItem, ['comment' => 'Komentar perlu ditinjau.', 'rating' => 2]);

    $this->actingAs($admin, 'admin')->patch(route('admin.reviews.approve', $approvedReview))
        ->assertSessionHas('success');
    $this->actingAs($admin, 'admin')->patch(route('admin.reviews.reject', $rejectedReview), ['reason' => 'Tidak sesuai panduan moderasi.'])
        ->assertSessionHas('success');

    expect($approvedReview->fresh()->status)->toBe(ReviewStatus::APPROVED)
        ->and($approvedReview->fresh()->reviewed_at)->not->toBeNull()
        ->and($rejectedReview->fresh()->status)->toBe(ReviewStatus::REJECTED)
        ->and($rejectedReview->fresh()->admin_note)->toBe('Tidak sesuai panduan moderasi.');

    $this->get(route('products.show', $approvedItem->product))
        ->assertSuccessful()
        ->assertSee('Sangat indah.');
    $this->get(route('products.show', $rejectedItem->product))
        ->assertSuccessful()
        ->assertDontSee('Komentar perlu ditinjau.');
});

test('the review moderation page defaults to pending reviews and validates rejection reasons', function () {
    $admin = Admin::create([
        'email' => 'moderator@example.com',
        'is_active' => true,
        'name' => 'Moderator Suki',
        'password' => 'password',
        'role' => AdminRole::ADMIN,
    ]);
    $pendingReview = app(ReviewService::class)->submit(deliveredOrderItemGroup(), ['rating' => 5]);

    $this->actingAs($admin, 'admin')->get(route('admin.reviews.index'))
        ->assertSuccessful()
        ->assertSee($pendingReview->reviewer_name);
    $this->actingAs($admin, 'admin')->patch(route('admin.reviews.reject', $pendingReview), [])
        ->assertSessionHasErrors('reason');
});
