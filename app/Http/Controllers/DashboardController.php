<?php

namespace App\Http\Controllers;

use App\Models\KpiDefinition;
use App\Models\KpiValue;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $year = (int) date('Y');

        $definitions = KpiDefinition::visibleTo($user)
            ->where('is_template', false)
            ->where('is_active', true)
            ->with('latestValue')
            ->get();

        $definitionIds = $definitions->pluck('id');

        // Status counts based on each KPI's latest value.
        $onTarget = $warning = $critical = 0;
        foreach ($definitions as $kpi) {
            if (! $kpi->latestValue) {
                continue;
            }
            match ($kpi->latestValue->status) {
                'critical' => $critical++,
                'warning' => $warning++,
                default => $onTarget++,
            };
        }
        $withData = $onTarget + $warning + $critical;

        // Monthly entry counts for the current year (DB-agnostic, computed in PHP).
        $monthlyRaw = KpiValue::whereIn('kpi_definition_id', $definitionIds)
            ->whereYear('recorded_at', $year)
            ->get(['recorded_at', 'value']);

        $monthlyTotals = collect(range(1, 12))->map(function ($m) use ($monthlyRaw) {
            $rows = $monthlyRaw->filter(fn ($v) => (int) $v->recorded_at->format('n') === $m);

            return [
                'month' => $m,
                'entries' => $rows->count(),
                'avg_value' => $rows->count() ? round($rows->avg('value'), 2) : 0,
            ];
        });

        // Enterprise Readiness + the connected KPIs (audit pillars, etc.).
        $connected = $definitions->where('is_connected', true)->values();

        $enterpriseReadiness = $this->presentKpi(
            $connected->firstWhere('source_key', 'enterprise_readiness')
        );

        $connectedKpis = $connected
            ->filter(fn ($k) => $k->source_key !== 'enterprise_readiness')
            ->map(fn ($k) => $this->presentKpi($k))
            ->values();

        $topKpis = $definitions
            ->filter(fn ($kpi) => $kpi->latestValue)
            ->sortByDesc(fn ($kpi) => abs((float) $kpi->latestValue->value))
            ->take(5)
            ->map(fn ($kpi) => $this->presentKpi($kpi))
            ->values();

        $recentValues = KpiValue::with('kpiDefinition:id,name_de,name_en,unit,category')
            ->whereIn('kpi_definition_id', $definitionIds)
            ->orderByDesc('recorded_at')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($v) => [
                'id' => $v->id,
                'kpi_id' => $v->kpi_definition_id,
                'kpi_name_de' => $v->kpiDefinition->name_de,
                'kpi_name_en' => $v->kpiDefinition->name_en,
                'value' => (float) $v->value,
                'unit' => $v->kpiDefinition->unit,
                'status' => $v->status,
                'recorded_at' => $v->recorded_at->format('Y-m-d'),
            ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'total_kpis' => $definitions->count(),
                'on_target' => $onTarget,
                'warning' => $warning,
                'critical' => $critical,
                'with_data' => $withData,
            ],
            'enterpriseReadiness' => $enterpriseReadiness,
            'connectedKpis' => $connectedKpis,
            'monthlyTotals' => $monthlyTotals,
            'topKpis' => $topKpis,
            'recentValues' => $recentValues,
            'year' => $year,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function presentKpi(?KpiDefinition $kpi): ?array
    {
        if (! $kpi) {
            return null;
        }

        $latest = $kpi->latestValue;

        return [
            'id' => $kpi->id,
            'name_de' => $kpi->name_de,
            'name_en' => $kpi->name_en,
            'unit' => $kpi->unit,
            'category' => $kpi->category,
            'source' => $kpi->source,
            'is_connected' => $kpi->is_connected,
            'scale_max' => $kpi->scale_max !== null ? (float) $kpi->scale_max : null,
            'target_value' => $kpi->target_value !== null ? (float) $kpi->target_value : null,
            'value' => $latest ? (float) $latest->value : null,
            'status' => $latest?->status,
            'recorded_at' => $latest?->recorded_at?->format('Y-m-d'),
        ];
    }
}
