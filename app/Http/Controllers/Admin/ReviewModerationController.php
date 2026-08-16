<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectReviewRequest;
use App\Http\Requests\Admin\ReviewIndexRequest;
use App\Models\Admin;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewModerationController extends Controller
{
    public function __construct(private ReviewService $reviewService) {}

    public function index(ReviewIndexRequest $request): View
    {
        $filters = $request->validated();
        $status = $filters['status'] ?? 'pending';

        $reviews = Review::query()
            ->with('product:id,name')
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.reviews.index', compact('reviews', 'status'));
    }

    public function approve(Review $review, Request $request): RedirectResponse
    {
        $this->reviewService->approve($review, $this->adminFromRequest($request));

        return back()->with('success', 'Ulasan berhasil disetujui.');
    }

    public function reject(Review $review, RejectReviewRequest $request): RedirectResponse
    {
        $this->reviewService->reject($review, $this->adminFromRequest($request), $request->validated('reason'));

        return back()->with('success', 'Ulasan ditolak dan alasannya telah disimpan.');
    }

    private function adminFromRequest(Request $request): Admin
    {
        $admin = $request->user('admin');

        abort_unless($admin instanceof Admin, 403);

        return $admin;
    }
}
