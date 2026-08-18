<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderIndexRequest;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Admin;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

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
            'itemGroups.variants',
            'paymentProofs' => fn ($query) => $query->with('verifier:id,name')->latest('uploaded_at'),
            'statusHistories' => fn ($query) => $query->with('changedBy:id,name')->orderBy('created_at')->orderBy('id'),
        ]);

        return view('admin.all-orders.show', [
            'order' => $order,
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
