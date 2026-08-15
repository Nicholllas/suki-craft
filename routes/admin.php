<?php

use App\Http\Controllers\Admin\AdminAccountController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\Auth\AdminAuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\AdminNewPasswordController;
use App\Http\Controllers\Admin\Auth\AdminPasswordResetLinkController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
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

        Route::view('orders', 'admin.orders.index')->name('orders.index');
        Route::resource('categories', CategoryController::class)->except(['destroy', 'show']);
        Route::patch('categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
        Route::resource('products', ProductController::class)->except(['destroy', 'show']);
        Route::patch('products/{product}/toggle', [ProductController::class, 'toggle'])->name('products.toggle');
        Route::delete('products/{product}/images/{image}', [ProductController::class, 'destroyImage'])->name('products.images.destroy');
        Route::view('inventory', 'admin.inventory.index')->name('inventory.index');
        Route::view('deliveries', 'admin.deliveries.index')->name('deliveries.index');
        Route::view('customers', 'admin.customers.index')->name('customers.index');
        Route::view('promotions', 'admin.promotions.index')->name('promotions.index');
        Route::view('reviews', 'admin.reviews.index')->name('reviews.index');
        Route::view('reports', 'admin.reports.index')->name('reports.index');
        Route::view('settings', 'admin.settings.index')->name('settings.index');

        Route::middleware('admin.role:super_admin')->group(function () {
            Route::resource('accounts', AdminAccountController::class)->except(['destroy', 'show'])->parameters(['accounts' => 'admin']);
            Route::patch('accounts/{admin}/deactivate', [AdminAccountController::class, 'deactivate'])->name('accounts.deactivate');
            Route::put('accounts/{admin}/password', [AdminAccountController::class, 'resetPassword'])->name('accounts.password.reset');
        });
    });
});
