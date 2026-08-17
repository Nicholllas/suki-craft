<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadPaymentProofRequest;
use App\Models\Order;
use App\Services\PaymentService;
use App\Services\QrisDynamicPayloadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private QrisDynamicPayloadService $qrisDynamicPayloadService,
    ) {}

    public function show(string $orderNumber, string $token): View
    {
        $order = $this->orderFromToken($orderNumber, $token);

        if ($this->paymentService->expireIfPaymentDeadlinePassed($order)) {
            $order->refresh();
        }

        $payment = $this->qrisDynamicPayloadService->paymentConfiguration();
        $order->load([
            'courier:id,name,phone',
            'items',
            'latestPaymentProof',
            'statusHistories' => fn ($query) => $query->orderBy('created_at')->orderBy('id'),
        ]);

        return view('orders.confirmation', [
            'deliveryProofUrl' => $order->delivery_proof_path ? route('orders.delivery-proofs.show', ['orderNumber' => $order->order_number, 'token' => $order->public_token]) : null,
            'order' => $order,
            'payment' => $payment,
            'qrisImageUrl' => $this->qrisDynamicPayloadService->isEnabled($payment)
                ? route('orders.qris.show', ['orderNumber' => $order->order_number, 'token' => $order->public_token])
                : null,
            'whatsAppUrl' => $this->whatsAppUrl($order),
        ]);
    }

    public function qris(string $orderNumber, string $token): Response
    {
        $order = $this->orderFromToken($orderNumber, $token);
        $svg = $this->qrisDynamicPayloadService->svgFor($order, $this->qrisDynamicPayloadService->paymentConfiguration());

        abort_unless($svg, 404);

        return response($svg, 200, [
            'Cache-Control' => 'private, no-store',
            'Content-Type' => 'image/svg+xml',
        ]);
    }

    public function store(UploadPaymentProofRequest $request, string $orderNumber, string $token): RedirectResponse
    {
        $order = $this->orderFromToken($orderNumber, $token);
        $this->paymentService->uploadProof($order, $request->file('proof'));

        return redirect()
            ->route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token])
            ->with('success', 'Bukti pembayaran berhasil dikirim dan sedang menunggu verifikasi.');
    }

    public function deliveryProof(string $orderNumber, string $token): StreamedResponse
    {
        $order = $this->orderFromToken($orderNumber, $token);
        abort_unless($order->delivery_proof_path, 404);

        return Storage::disk('local')->response($order->delivery_proof_path);
    }

    private function orderFromToken(string $orderNumber, string $token): Order
    {
        return Order::query()
            ->where('order_number', $orderNumber)
            ->where('public_token', $token)
            ->firstOrFail();
    }

    private function whatsAppUrl(Order $order): ?string
    {
        $number = Str::of((string) config('payment.whatsapp_number'))->replaceMatches('/\D+/', '')->toString();

        if (blank($number)) {
            return null;
        }

        $message = 'Halo Suki Craft, saya sudah melakukan pembayaran untuk pesanan '.$order->order_number.' dengan total Rp'.number_format($order->total, 0, ',', '.').'.';

        return 'https://wa.me/'.$number.'?text='.urlencode($message);
    }
}
