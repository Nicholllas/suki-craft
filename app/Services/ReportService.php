<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportService
{
    public function getSalesSummary(Carbon $from, Carbon $to): array
    {
        $summary = $this->paidOrdersBetween($from, $to)->toBase()->selectRaw('COALESCE(SUM(total), 0) as revenue, COUNT(*) as orders, COALESCE(AVG(total), 0) as average_order')->first();

        return ['average_order' => (float) $summary->average_order, 'orders' => (int) $summary->orders, 'revenue' => (float) $summary->revenue];
    }

    public function getSalesTrend(Carbon $from, Carbon $to, string $groupBy): Collection
    {
        $expression = match ($groupBy) {
            'week' => 'YEARWEEK(created_at, 1)',
            'month' => "DATE_FORMAT(created_at, '%Y-%m')",
            default => 'DATE(created_at)',
        };
        $revenue = $this->paidOrdersBetween($from, $to)->toBase()->selectRaw("{$expression} as period, COALESCE(SUM(total), 0) as revenue")->groupBy('period')->orderBy('period')->pluck('revenue', 'period');

        return $this->trendPeriods($from, $to, $groupBy)->map(fn (Carbon $date): array => [
            'label' => match ($groupBy) {
                'week' => 'Minggu '.$date->isoWeek(),
                'month' => $date->locale('id')->translatedFormat('M Y'),
                default => $date->locale('id')->translatedFormat('d M'),
            },
            'revenue' => (float) $revenue->get(match ($groupBy) {
                'week' => $date->isoWeekYear().str_pad((string) $date->isoWeek(), 2, '0', STR_PAD_LEFT),
                'month' => $date->format('Y-m'),
                default => $date->toDateString(),
            }, 0),
        ])->values();
    }

    public function getTopProducts(Carbon $from, Carbon $to, int $limit = 10): Collection
    {
        return OrderItem::query()->whereIn('order_id', $this->paidOrderIdsBetween($from, $to))->selectRaw('product_id, product_name, SUM(quantity) as quantity_sold, COALESCE(SUM(subtotal), 0) as revenue')->groupBy('product_id', 'product_name')->orderByDesc('quantity_sold')->orderByDesc('revenue')->limit($limit)->get();
    }

    public function getRevenueByCategory(Carbon $from, Carbon $to): Collection
    {
        $orderItems = (new OrderItem)->getTable();
        $products = (new Product)->getTable();
        $categories = (new Category)->getTable();

        return OrderItem::query()->from($orderItems)->join($products, "{$products}.id", '=', "{$orderItems}.product_id")->leftJoin($categories, "{$categories}.id", '=', "{$products}.category_id")->whereIn("{$orderItems}.order_id", $this->paidOrderIdsBetween($from, $to))->selectRaw("COALESCE({$categories}.name, 'Tanpa kategori') as category_name, COALESCE(SUM({$orderItems}.subtotal), 0) as revenue")->groupBy("{$categories}.id", "{$categories}.name")->orderByDesc('revenue')->get();
    }

    private function paidOrderIdsBetween(Carbon $from, Carbon $to): Builder
    {
        return $this->paidOrdersBetween($from, $to)->select('id');
    }

    private function paidOrdersBetween(Carbon $from, Carbon $to): Builder
    {
        return Order::query()->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()])->whereIn('status', [OrderStatus::PAYMENT_CONFIRMED, OrderStatus::PROCESSING, OrderStatus::OUT_FOR_DELIVERY, OrderStatus::DELIVERED]);
    }

    private function trendPeriods(Carbon $from, Carbon $to, string $groupBy): Collection
    {
        $start = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        return match ($groupBy) {
            'week' => collect(CarbonPeriod::create($start->startOfWeek(), '1 week', $end->endOfWeek())),
            'month' => collect(CarbonPeriod::create($start->startOfMonth(), '1 month', $end->startOfMonth())),
            default => collect(CarbonPeriod::create($start, $end)),
        };
    }
}
