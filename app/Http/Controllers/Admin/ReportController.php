<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReportRequest;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index(ReportRequest $request): View
    {
        $filters = $request->validated();
        $from = Carbon::createFromFormat('Y-m-d', $filters['from']);
        $to = Carbon::createFromFormat('Y-m-d', $filters['to']);
        $groupBy = $filters['group_by'] ?? $this->suggestGroupBy($from, $to);

        return view('admin.reports.index', [
            'filters' => $filters,
            'groupBy' => $groupBy,
            'revenueByCategory' => $this->reportService->getRevenueByCategory($from, $to),
            'summary' => $this->reportService->getSalesSummary($from, $to),
            'topProducts' => $this->reportService->getTopProducts($from, $to),
            'trend' => $this->reportService->getSalesTrend($from, $to, $groupBy),
        ]);
    }

    private function suggestGroupBy(Carbon $from, Carbon $to): string
    {
        return $from->diffInDays($to) > 90 ? 'month' : ($from->diffInDays($to) > 31 ? 'week' : 'day');
    }
}
