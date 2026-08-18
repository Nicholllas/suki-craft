<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectPaymentProofRequest;
use App\Models\Admin;
use App\Models\Order;
use App\Models\PaymentProof;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentVerificationController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function index(): View
    {
        $paymentProof = new PaymentProof;

        $orders = Order::query()
            ->where('status', OrderStatus::AWAITING_VERIFICATION)
            ->with(['latestPaymentProof' => fn ($query) => $query->select([
                $paymentProof->qualifyColumn('id'),
                $paymentProof->qualifyColumn('order_id'),
                $paymentProof->qualifyColumn('path'),
                $paymentProof->qualifyColumn('status'),
                $paymentProof->qualifyColumn('uploaded_at'),
            ])])
            ->withMax('paymentProofs as latest_proof_uploaded_at', 'uploaded_at')
            ->orderBy('latest_proof_uploaded_at')
            ->paginate(15);

        return view('admin.orders.index', ['orders' => $orders]);
    }

    public function show(Order $order): View
    {
        $order->load([
            'itemGroups.variants',
            'paymentProofs' => fn ($query) => $query->with('verifier')->latest('uploaded_at'),
        ]);

        return view('admin.orders.show', ['order' => $order]);
    }

    public function approve(Order $order, PaymentProof $paymentProof, Request $request): RedirectResponse
    {
        $this->ensurePaymentProofBelongsToOrder($order, $paymentProof);
        $this->paymentService->approve($paymentProof, $this->adminFromRequest($request));

        return redirect()->route('admin.payment-verifications.show', $order)->with('success', 'Pembayaran berhasil dikonfirmasi.');
    }

    public function reject(Order $order, PaymentProof $paymentProof, RejectPaymentProofRequest $request): RedirectResponse
    {
        $this->ensurePaymentProofBelongsToOrder($order, $paymentProof);
        $this->paymentService->reject($paymentProof, $this->adminFromRequest($request), $request->validated('reason'));

        return redirect()->route('admin.payment-verifications.show', $order)->with('success', 'Bukti pembayaran ditolak dan pelanggan dapat mengunggah ulang.');
    }

    public function preview(Order $order, PaymentProof $paymentProof): StreamedResponse
    {
        $this->ensurePaymentProofBelongsToOrder($order, $paymentProof);

        return Storage::disk('local')->response($paymentProof->path);
    }

    private function adminFromRequest(Request $request): Admin
    {
        $admin = $request->user('admin');

        abort_unless($admin instanceof Admin, 403);

        return $admin;
    }

    private function ensurePaymentProofBelongsToOrder(Order $order, PaymentProof $paymentProof): void
    {
        abort_unless($paymentProof->order_id === $order->id, 404);
    }
}
