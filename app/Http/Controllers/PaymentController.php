<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadPaymentProofRequest;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function show(string $orderNumber, string $token): View
    {
        $order = $this->orderFromToken($orderNumber, $token);
        $order->load(['items', 'latestPaymentProof']);

        return view('orders.confirmation', [
            'order' => $order,
            'payment' => config('payment'),
            'whatsAppUrl' => $this->whatsAppUrl($order),
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
