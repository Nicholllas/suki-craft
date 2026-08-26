<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderWhatsAppService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderHistoryController extends Controller
{
    public function __construct(
        private OrderWhatsAppService $orderWhatsAppService,
        private PaymentService $paymentService,
    ) {}

    public function index(Request $request): View
    {
        $orders = Order::query()
            ->whereBelongsTo($request->user('customer'), 'customer')

            ->latest()
            ->paginate(10);

        $orders->getCollection()->each(function (Order $order): void {
            $order->setAttribute('whats_app_url', $this->orderWhatsAppService->followUpUrl($order));
        });

        return view('customer.orders.index', ['orders' => $orders]);
    }

    public function show(Request $request, Order $order): View
    {
        $order = $this->ownedOrder($request, $order);

        if ($this->paymentService->expireIfPaymentDeadlinePassed($order)) {
            $order->refresh();
        }

        $order->load([
            'courier:id,name,phone',
            'itemGroups.bouquetSize',
            'itemGroups.customRequest',
            'itemGroups.review',
            'itemGroups.variants',
            'statusHistories' => fn ($query) => $query->orderBy('created_at')->orderBy('id'),
        ]);

        return view('customer.orders.show', [
            'deliveryProofUrl' => $order->delivery_proof_path ? route('customer.orders.delivery-proof', $order) : null,
            'order' => $order,
            'whatsAppUrl' => $this->orderWhatsAppService->followUpUrl($order),
        ]);
    }

    public function deliveryProof(Request $request, Order $order): StreamedResponse
    {
        $order = $this->ownedOrder($request, $order);
        abort_unless($order->delivery_proof_path, 404);

        return Storage::disk('local')->response($order->delivery_proof_path);
    }

    private function ownedOrder(Request $request, Order $order): Order
    {
        return Order::query()->whereBelongsTo($request->user('customer'), 'customer')->findOrFail($order->id);
    }
}
