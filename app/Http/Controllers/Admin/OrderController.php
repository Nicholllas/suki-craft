<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderIndexRequest;
use App\Http\Requests\Admin\QuoteOrderRequest;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Admin;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\QuoteService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService, private QuoteService $quoteService) {}

    public function index(OrderIndexRequest $request): View
    {
        $filters = $request->validated();
        $orders = Order::query()
            ->with('courier:id,name')
            ->when($filters['statuses'] ?? null, fn (Builder $query, array $statuses) => $query->whereIn('status', $statuses))
            ->when($filters['order_date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['order_date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->when($filters['delivery_date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('delivery_date', '>=', $date))
            ->when($filters['delivery_date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('delivery_date', '<=', $date))
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(function (Builder $query) use ($search): void {
                $query->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.all-orders.index', [
            'filters' => $filters,
            'orders' => $orders,
            'statusOptions' => OrderStatus::cases(),
            'summaries' => $this->summaries(),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load([
            'courier:id,name,phone',
            'itemGroups.bouquetSize',
            'itemGroups.customRequest',
            'itemGroups.variants',
            'paymentProofs' => fn ($query) => $query->with('verifier:id,name')->latest('uploaded_at'),
            'statusHistories' => fn ($query) => $query->with('changedBy:id,name')->orderBy('created_at')->orderBy('id'),
        ]);

        return view('admin.all-orders.show', [
            'order' => $order,
            'quoteWhatsAppUrl' => $this->quoteWhatsAppUrl($order),
            'statusOptions' => OrderStatus::cases(),
        ]);
    }

    public function updateStatus(Order $order, UpdateOrderStatusRequest $request): RedirectResponse
    {
        $this->orderService->overrideStatus(
            $order,
            $request->validated('status'),
            $this->adminFromRequest($request),
            $request->validated('reason'),
        );

        return redirect()->route('admin.orders.show', $order)->with('success', 'Status pesanan berhasil diperbarui secara manual.');
    }

    public function quote(Order $order, QuoteOrderRequest $request): RedirectResponse
    {
        $this->quoteService->quote($order, $request->validated(), $this->adminFromRequest($request));

        return redirect()->route('admin.orders.show', $order)->with('success', 'Penawaran harga berhasil disimpan. Bagikan link penawaran kepada pelanggan melalui WhatsApp.');
    }

    public function deliveryProof(Order $order): StreamedResponse
    {
        abort_unless($order->delivery_proof_path, 404);

        return Storage::disk('local')->response($order->delivery_proof_path);
    }

    private function adminFromRequest(Request $request): Admin
    {
        $admin = $request->user('admin');

        abort_unless($admin instanceof Admin, 403);

        return $admin;
    }

    private function quoteWhatsAppUrl(Order $order): ?string
    {
        if ($order->status !== OrderStatus::AWAITING_APPROVAL) {
            return null;
        }

        $phone = Str::of((string) $order->customer_phone)->replaceMatches('/\D+/', '')->toString();

        if (blank($phone)) {
            return null;
        }

        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        $approvalUrl = route('orders.confirmation', ['orderNumber' => $order->order_number, 'token' => $order->public_token]);
        $expiresAt = $order->quote_expires_at?->locale('id')->translatedFormat('d M Y, H.i').' WIB';
        $message = "Halo {$order->customer_name}, penawaran untuk pesanan {$order->order_number} sudah tersedia.\n\nTotal penawaran: Rp".number_format((int) $order->total, 0, ',', '.')."\nBerlaku sampai: {$expiresAt}\n\nSetujui penawaran dan lanjutkan pembayaran melalui link ini:\n{$approvalUrl}";

        return 'https://wa.me/'.$phone.'?text='.urlencode($message);
    }

    private function summaries(): array
    {
        $statusCounts = Order::query()->selectRaw('status, count(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status');

        return [
            ['key' => 'pending_payment', 'label' => 'Menunggu pembayaran', 'value' => (int) $statusCounts->get(OrderStatus::PENDING_PAYMENT->value, 0)],
            ['key' => 'awaiting_verification', 'label' => 'Menunggu verifikasi', 'value' => (int) $statusCounts->get(OrderStatus::AWAITING_VERIFICATION->value, 0)],
            ['key' => 'processing', 'label' => 'Sedang dirangkai', 'value' => (int) $statusCounts->get(OrderStatus::PROCESSING->value, 0)],
            ['key' => 'out_for_delivery', 'label' => 'Dalam pengiriman', 'value' => (int) $statusCounts->get(OrderStatus::OUT_FOR_DELIVERY->value, 0)],
            ['key' => 'delivered_today', 'label' => 'Selesai hari ini', 'value' => Order::query()->where('status', OrderStatus::DELIVERED)->whereDate('delivered_at', today())->count()],
            ['key' => 'cancelled', 'label' => 'Dibatalkan', 'value' => (int) $statusCounts->get(OrderStatus::CANCELLED->value, 0)],
        ];
    }
}
