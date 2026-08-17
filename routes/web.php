<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Customer\OrderHistoryController;
use App\Http\Controllers\Customer\ProfileController as CustomerProfileController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'index'])->name('home');

Route::view('/tentang-kami', 'store.about')->name('about');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/items', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/items/{cartItem}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/items/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::post('/checkout/promotion', [CheckoutController::class, 'validatePromotion'])->middleware('throttle:10,1')->name('checkout.promotions.validate');
Route::get('/orders/{orderNumber}/confirmation/{token}', [PaymentController::class, 'show'])->name('orders.confirmation');
Route::post('/orders/{orderNumber}/confirmation/{token}/payment-proofs', [PaymentController::class, 'store'])->middleware('throttle:10,1')->name('orders.payment-proofs.store');
Route::get('/orders/{orderNumber}/confirmation/{token}/delivery-proof', [PaymentController::class, 'deliveryProof'])->name('orders.delivery-proofs.show');
Route::get('/lacak-pesanan', [TrackingController::class, 'create'])->name('tracking.create');
Route::post('/lacak-pesanan', [TrackingController::class, 'store'])->middleware('throttle:10,1')->name('tracking.store');
Route::get('/lacak-pesanan/{order}', [TrackingController::class, 'show'])->name('tracking.show');
Route::get('/lacak-pesanan/{order}/bukti-pengiriman', [TrackingController::class, 'deliveryProof'])->name('tracking.delivery-proofs.show');
Route::post('/lacak-pesanan/{order}/items/{orderItem}/ulasan', [ReviewController::class, 'storeForTracking'])->middleware('throttle:10,1')->name('tracking.reviews.store');

Route::prefix('akun')->name('customer.')->group(function () {
    Route::middleware('guest:customer')->group(function () {
        Route::get('daftar', [CustomerAuthController::class, 'createRegistration'])->name('register');
        Route::post('daftar', [CustomerAuthController::class, 'register'])->name('register.store');
        Route::get('masuk', [CustomerAuthController::class, 'createLogin'])->name('login');
        Route::post('masuk', [CustomerAuthController::class, 'login'])->name('login.store');
        Route::get('lupa-password', [CustomerAuthController::class, 'createPasswordResetLink'])->name('password.request');
        Route::post('lupa-password', [CustomerAuthController::class, 'sendPasswordResetLink'])->name('password.email');
        Route::get('reset-password/{token}', [CustomerAuthController::class, 'createNewPassword'])->name('password.reset');
        Route::post('reset-password', [CustomerAuthController::class, 'resetPassword'])->name('password.store');
    });

    Route::middleware('auth:customer')->group(function () {
        Route::post('keluar', [CustomerAuthController::class, 'logout'])->name('logout');
        Route::get('profil', [CustomerProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profil', [CustomerProfileController::class, 'update'])->name('profile.update');
        Route::put('profil/password', [CustomerProfileController::class, 'updatePassword'])->name('profile.password.update');
        Route::get('pesanan', [OrderHistoryController::class, 'index'])->name('orders.index');
        Route::get('pesanan/{order}', [OrderHistoryController::class, 'show'])->name('orders.show');
        Route::get('pesanan/{order}/bukti-pengiriman', [OrderHistoryController::class, 'deliveryProof'])->name('orders.delivery-proof');
        Route::post('pesanan/{order}/items/{orderItem}/ulasan', [ReviewController::class, 'storeForCustomer'])->middleware('throttle:10,1')->name('orders.reviews.store');
    });
});

Route::redirect('/dashboard', '/')->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
