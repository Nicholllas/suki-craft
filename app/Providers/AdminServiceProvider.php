<?php

namespace App\Providers;

use App\Enums\CustomRequestStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentProofStatus;
use App\Enums\ReviewStatus;
use App\Models\CustomRequest;
use App\Models\PaymentProof;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        ViewFacade::composer('layouts.admin', function (View $view): void {
            $pendingPaymentVerificationCount = auth('admin')->check()
                ? PaymentProof::query()
                    ->where('status', PaymentProofStatus::PENDING)
                    ->whereHas('order', fn ($query) => $query->where('status', OrderStatus::AWAITING_VERIFICATION))
                    ->count()
                : 0;
            $pendingCustomRequestCount = auth('admin')->check()
                ? CustomRequest::query()
                    ->whereIn('status', [CustomRequestStatus::WAITING_REVIEW, CustomRequestStatus::REVISION_REQUESTED])
                    ->count()
                : 0;
            $pendingReviewCount = auth('admin')->check()
                ? Review::query()->where('status', ReviewStatus::PENDING)->count()
                : 0;

            $view->with([
                'adminNotificationCount' => $pendingPaymentVerificationCount + $pendingCustomRequestCount,
                'pendingCustomRequestCount' => $pendingCustomRequestCount,
                'pendingPaymentVerificationCount' => $pendingPaymentVerificationCount,
                'pendingReviewCount' => $pendingReviewCount,
            ]);
        });
    }
}
