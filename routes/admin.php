<?php

use App\Http\Controllers\Admin\AdminAccountController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\Auth\AdminAuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\AdminNewPasswordController;
use App\Http\Controllers\Admin\Auth\AdminPasswordResetLinkController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CourierController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentVerificationController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AdminAuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AdminAuthenticatedSessionController::class, 'store'])->name('login.store');
        Route::get('forgot-password', [AdminPasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('forgot-password', [AdminPasswordResetLinkController::class, 'store'])->name('password.email');
        Route::get('reset-password/{token}', [AdminNewPasswordController::class, 'create'])->name('password.reset');
        Route::post('reset-password', [AdminNewPasswordController::class, 'store'])->name('password.store');
    });

    Route::middleware(['auth:admin', 'admin.active'])->group(function () {
        Route::post('logout', [AdminAuthenticatedSessionController::class, 'destroy'])->name('logout');
        Route::get('dashboard', DashboardController::class)->name('dashboard');
        Route::get('profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password.update');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
        Route::get('orders/{order}/delivery-proof', [OrderController::class, 'deliveryProof'])->name('orders.delivery-proof');
        Route::get('payment-verifications', [PaymentVerificationController::class, 'index'])->name('payment-verifications.index');
        Route::get('payment-verifications/{order}', [PaymentVerificationController::class, 'show'])->name('payment-verifications.show');
        Route::get('payment-verifications/{order}/payment-proofs/{paymentProof}/preview', [PaymentVerificationController::class, 'preview'])->name('payment-verifications.payment-proofs.preview');
        Route::patch('payment-verifications/{order}/payment-proofs/{paymentProof}/approve', [PaymentVerificationController::class, 'approve'])->name('payment-verifications.payment-proofs.approve');
        Route::patch('payment-verifications/{order}/payment-proofs/{paymentProof}/reject', [PaymentVerificationController::class, 'reject'])->name('payment-verifications.payment-proofs.reject');
        Route::resource('categories', CategoryController::class)->except(['destroy', 'show']);
        Route::patch('categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
        Route::resource('products', ProductController::class)->except(['destroy', 'show']);
        Route::patch('products/{product}/toggle', [ProductController::class, 'toggle'])->name('products.toggle');
        Route::delete('products/{product}/images/{image}', [ProductController::class, 'destroyImage'])->name('products.images.destroy');
        Route::resource('ingredients', IngredientController::class)->except(['destroy']);
        Route::patch('ingredients/{ingredient}/toggle', [IngredientController::class, 'toggle'])->name('ingredients.toggle');
        Route::post('ingredients/{ingredient}/stock-in', [IngredientController::class, 'stockIn'])->name('ingredients.stock-in');
        Route::post('ingredients/{ingredient}/adjustment', [IngredientController::class, 'adjust'])->name('ingredients.adjustment');
        Route::get('deliveries', [DeliveryController::class, 'index'])->name('deliveries.index');
        Route::patch('deliveries/{order}/courier', [DeliveryController::class, 'assignCourier'])->name('deliveries.courier.assign');
        Route::patch('deliveries/{order}/processing', [DeliveryController::class, 'markProcessing'])->name('deliveries.processing');
        Route::patch('deliveries/{order}/out-for-delivery', [DeliveryController::class, 'markOutForDelivery'])->name('deliveries.out_for_delivery');
        Route::patch('deliveries/{order}/delivered', [DeliveryController::class, 'markDelivered'])->name('deliveries.delivered');
        Route::patch('deliveries/{order}/cancel', [DeliveryController::class, 'cancel'])->name('deliveries.cancel');
        Route::resource('couriers', CourierController::class)->except(['create', 'destroy', 'edit', 'show']);
        Route::patch('couriers/{courier}/toggle', [CourierController::class, 'toggle'])->name('couriers.toggle');
        Route::view('customers', 'admin.customers.index')->name('customers.index');
        Route::view('promotions', 'admin.promotions.index')->name('promotions.index');
        Route::view('reviews', 'admin.reviews.index')->name('reviews.index');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::view('settings', 'admin.settings.index')->name('settings.index');

        Route::middleware('admin.role:super_admin')->group(function () {
            Route::resource('accounts', AdminAccountController::class)->except(['destroy', 'show'])->parameters(['accounts' => 'admin']);
            Route::patch('accounts/{admin}/deactivate', [AdminAccountController::class, 'deactivate'])->name('accounts.deactivate');
            Route::put('accounts/{admin}/password', [AdminAccountController::class, 'resetPassword'])->name('accounts.password.reset');
        });
    });
});
