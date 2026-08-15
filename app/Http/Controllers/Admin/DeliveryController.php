<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignDeliveryCourierRequest;
use App\Http\Requests\Admin\CancelDeliveryRequest;
use App\Http\Requests\Admin\DeliveryScheduleRequest;
use App\Http\Requests\Admin\MarkDeliveredRequest;
use App\Models\Admin;
use App\Models\Courier;
use App\Models\Order;
use App\Services\DeliveryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    public function __construct(private DeliveryService $deliveryService) {}

    public function index(DeliveryScheduleRequest $request): View
    {
        $filters = $request->validated();
        $date = $filters['date'] ?? today()->toDateString();
        $timeSlot = $filters['time_slot'] ?? null;
        $statuses = [OrderStatus::PAYMENT_CONFIRMED, OrderStatus::PROCESSING, OrderStatus::OUT_FOR_DELIVERY];

        $orders = Order::query()
            ->whereDate('delivery_date', $date)
            ->whereIn('status', $statuses)
            ->when($timeSlot, fn ($query) => $query->where('delivery_time_slot', $timeSlot))
            ->with('courier:id,name,phone')
            ->orderBy('delivery_date')
            ->orderBy('delivery_time_slot')
            ->oldest()
            ->paginate(20)
            ->withQueryString();
        $couriers = Courier::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'phone']);

        return view('admin.deliveries.index', [
            'couriers' => $couriers,
            'date' => $date,
            'orders' => $orders,
            'timeSlot' => $timeSlot,
            'timeSlots' => config('delivery.time_slots'),
        ]);
    }

    public function assignCourier(Order $order, AssignDeliveryCourierRequest $request): RedirectResponse
    {
        $courier = Courier::query()->findOrFail($request->validated('courier_id'));
        $this->deliveryService->assignCourier($order, $courier, $this->adminFromRequest($request));

        return back()->with('success', 'Kurir berhasil ditugaskan.');
    }

    public function markProcessing(Order $order, Request $request): RedirectResponse
    {
        $this->deliveryService->markProcessing($order, $this->adminFromRequest($request));

        return back()->with('success', 'Pesanan sekarang sedang disiapkan.');
    }

    public function markOutForDelivery(Order $order, Request $request): RedirectResponse
    {
        $this->deliveryService->markOutForDelivery($order, $this->adminFromRequest($request));

        return back()->with('success', 'Pesanan ditandai sedang dalam pengiriman.');
    }

    public function markDelivered(Order $order, MarkDeliveredRequest $request): RedirectResponse
    {
        $this->deliveryService->markDelivered($order, $request->file('proof_photo'), $this->adminFromRequest($request));

        return back()->with('success', 'Pesanan berhasil ditandai telah diterima.');
    }

    public function cancel(Order $order, CancelDeliveryRequest $request): RedirectResponse
    {
        $this->deliveryService->markCancelled($order, $request->validated('reason'), $this->adminFromRequest($request));

        return back()->with('success', 'Pesanan berhasil dibatalkan.');
    }

    private function adminFromRequest(Request $request): Admin
    {
        $admin = $request->user('admin');

        abort_unless($admin instanceof Admin, 403);

        return $admin;
    }
}
