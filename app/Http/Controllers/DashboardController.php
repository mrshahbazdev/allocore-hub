<?php

namespace App\Http\Controllers;

use App\Models\KpiDefinition;
use App\Models\KpiMonthlyTarget;
use App\Models\KpiValue;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $year = (int) date('Y');
        $currentMonth = (int) date('m');

        $totalKpis = KpiDefinition::where('is_template', false)->where('is_active', true)->count();

        $latestStatuses = DB::table('kpi_values as v')
            ->join(DB::raw('(SELECT kpi_definition_id, MAX(recorded_at) as max_date FROM kpi_values GROUP BY kpi_definition_id) as latest'), function ($join) {
                $join->on('v.kpi_definition_id', '=', 'latest.kpi_definition_id')
                     ->on('v.recorded_at', '=', 'latest.max_date');
            })
            ->select('v.status', DB::raw('COUNT(*) as count'))
            ->groupBy('v.status')
            ->pluck('count', 'status');

        $onTarget = $latestStatuses->get('on_target', 0);
        $warning = $latestStatuses->get('warning', 0);
        $critical = $latestStatuses->get('critical', 0);
        $withData = $onTarget + $warning + $critical;

        $monthlyTotals = KpiValue::whereYear('recorded_at', $year)
            ->select(
                DB::raw('EXTRACT(MONTH FROM recorded_at) as month'),
                DB::raw('COUNT(*) as entries'),
                DB::raw('AVG(value) as avg_value')
            )
            ->groupBy(DB::raw('EXTRACT(MONTH FROM recorded_at)'))
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => [
                'month' => (int) $row->month,
                'entries' => $row->entries,
                'avg_value' => round((float) $row->avg_value, 2),
            ]);

        $topKpis = KpiDefinition::where('is_template', false)
            ->where('is_active', true)
            ->with('latestValue')
            ->get()
            ->filter(fn ($kpi) => $kpi->latestValue)
            ->sortByDesc(fn ($kpi) => abs((float) $kpi->latestValue->value))
            ->take(5)
            ->values()
            ->map(fn ($kpi) => [
                'id' => $kpi->id,
                'name_de' => $kpi->name_de,
                'name_en' => $kpi->name_en,
                'unit' => $kpi->unit,
                'category' => $kpi->category,
                'value' => (float) $kpi->latestValue->value,
                'status' => $kpi->latestValue->status,
                'recorded_at' => $kpi->latestValue->recorded_at->format('Y-m-d'),
            ]);

        $recentValues = KpiValue::with('kpiDefinition:id,name_de,name_en,unit,category')
            ->orderByDesc('recorded_at')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($v) => [
                'id' => $v->id,
                'kpi_name_de' => $v->kpiDefinition->name_de,
                'kpi_name_en' => $v->kpiDefinition->name_en,
                'kpi_id' => $v->kpi_definition_id,
                'value' => (float) $v->value,
                'unit' => $v->kpiDefinition->unit,
                'status' => $v->status,
                'recorded_at' => $v->recorded_at->format('Y-m-d'),
            ]);

        $categoryBreakdown = KpiDefinition::where('is_template', false)
            ->where('is_active', true)
            ->select('category', DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->orderByDesc('count')
            ->get()
            ->map(fn ($row) => [
                'category' => $row->category ?: 'Uncategorized',
                'count' => $row->count,
            ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_kpis' => $totalKpis,
                'on_target' => $onTarget,
                'warning' => $warning,
                'critical' => $critical,
                'with_data' => $withData,
            ],
            'monthlyTotals' => $monthlyTotals,
            'topKpis' => $topKpis,
            'recentValues' => $recentValues,
            'categoryBreakdown' => $categoryBreakdown,
            'year' => $year,
        ]);
    }
}
