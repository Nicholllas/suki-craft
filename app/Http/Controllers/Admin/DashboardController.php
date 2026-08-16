<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DashboardRequest;
use App\Models\Order;
use App\Services\InventoryService;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function __invoke(DashboardRequest $request): View
    {
        $period = $request->validated('period', 'week');
        $periodStart = $this->periodStart($period);
        $paidStatuses = $this->paidStatuses();
        $revenue = $this->revenue($paidStatuses);
        $statusCounts = $this->statusCounts();
        $lowStockIngredients = $this->inventoryService->getLowStockIngredients();

        return view('admin.dashboard', [
            'awaitingVerificationCount' => (int) $statusCounts->get(OrderStatus::AWAITING_VERIFICATION->value, 0),
            'latestOrders' => Order::query()->select(['id', 'order_number', 'customer_name', 'customer_phone', 'total', 'status', 'created_at'])->latest()->limit(5)->get(),
            'lowStockIngredientCount' => $lowStockIngredients->count(),
            'lowStockIngredients' => $lowStockIngredients->take(5),
            'newOrderCount' => Order::query()->whereBetween('created_at', [$periodStart, now()])->count(),
            'period' => $period,
            'periodLabel' => $this->periodLabel($period),
            'revenueMonth' => (float) $revenue->month_revenue,
            'revenueToday' => (float) $revenue->today_revenue,
            'statusSummaries' => $this->statusSummaries($statusCounts),
            'totalOrderCount' => $statusCounts->sum(),
            'trend' => $this->trend($periodStart, now(), $paidStatuses),
        ]);
    }

    private function paidStatuses(): array
    {
        return [
            OrderStatus::PAYMENT_CONFIRMED->value,
            OrderStatus::PROCESSING->value,
            OrderStatus::OUT_FOR_DELIVERY->value,
            OrderStatus::DELIVERED->value,
        ];
    }

    private function periodLabel(string $period): string
    {
        return match ($period) {
            'today' => 'Hari ini',
            'month' => 'Bulan ini',
            default => 'Minggu ini',
        };
    }

    private function periodStart(string $period): CarbonInterface
    {
        return match ($period) {
            'today' => today(),
            'month' => now()->startOfMonth(),
            default => now()->startOfWeek(),
        };
    }

    private function revenue(array $paidStatuses): object
    {
        return Order::query()
            ->toBase()
            ->whereIn('status', $paidStatuses)
            ->selectRaw('COALESCE(SUM(CASE WHEN created_at >= ? THEN total ELSE 0 END), 0) as today_revenue', [today()])
            ->selectRaw('COALESCE(SUM(CASE WHEN created_at >= ? THEN total ELSE 0 END), 0) as month_revenue', [now()->startOfMonth()])
            ->first();
    }

    private function statusCounts(): Collection
    {
        return Order::query()->toBase()->selectRaw('status, COUNT(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status');
    }

    private function statusSummaries(Collection $statusCounts): array
    {
        return [
            ['label' => 'Menunggu', 'tone' => 'amber', 'value' => (int) $statusCounts->get(OrderStatus::PENDING_PAYMENT->value, 0) + (int) $statusCounts->get(OrderStatus::AWAITING_VERIFICATION->value, 0)],
            ['label' => 'Diproses', 'tone' => 'sky', 'value' => (int) $statusCounts->get(OrderStatus::PAYMENT_CONFIRMED->value, 0) + (int) $statusCounts->get(OrderStatus::PROCESSING->value, 0)],
            ['label' => 'Dikirim', 'tone' => 'violet', 'value' => (int) $statusCounts->get(OrderStatus::OUT_FOR_DELIVERY->value, 0)],
            ['label' => 'Selesai', 'tone' => 'emerald', 'value' => (int) $statusCounts->get(OrderStatus::DELIVERED->value, 0)],
            ['label' => 'Dibatalkan', 'tone' => 'rose', 'value' => (int) $statusCounts->get(OrderStatus::CANCELLED->value, 0)],
        ];
    }

    private function trend(CarbonInterface $start, CarbonInterface $end, array $paidStatuses): Collection
    {
        $revenueByDate = Order::query()
            ->toBase()
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('status', $paidStatuses)
            ->selectRaw('DATE(created_at) as date, COALESCE(SUM(total), 0) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('revenue', 'date');

        return collect(CarbonPeriod::create($start->toDateString(), $end->toDateString()))->map(fn (CarbonInterface $date): array => [
            'label' => $date->locale('id')->translatedFormat('d M'),
            'revenue' => (float) $revenueByDate->get($date->toDateString(), 0),
        ]);
    }
}
