<?php

namespace App\Http\Controllers;

use App\Models\KpiDefinition;
use App\Models\KpiMonthlyTarget;
use App\Models\KpiValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class KpiSpreadsheetController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) ($request->input('year') ?: date('Y'));
        $categoryFilter = $request->input('category');

        $query = KpiDefinition::where('is_template', false)
            ->where('is_active', true);

        if ($categoryFilter) {
            $query->where('category', $categoryFilter);
        }

        $kpis = $query->orderBy('category')->orderBy('name_de')->get();

        $kpiIds = $kpis->pluck('id');

        $actuals = KpiValue::whereIn('kpi_definition_id', $kpiIds)
            ->whereYear('recorded_at', $year)
            ->select('kpi_definition_id', DB::raw('EXTRACT(MONTH FROM recorded_at) as month'), DB::raw('AVG(value) as avg_value'))
            ->groupBy('kpi_definition_id', DB::raw('EXTRACT(MONTH FROM recorded_at)'))
            ->get()
            ->groupBy('kpi_definition_id')
            ->map(function ($group) {
                $months = [];
                foreach ($group as $row) {
                    $months[(int) $row->month] = round((float) $row->avg_value, 4);
                }
                return $months;
            });

        $targets = KpiMonthlyTarget::whereIn('kpi_definition_id', $kpiIds)
            ->where('year', $year)
            ->get()
            ->groupBy('kpi_definition_id')
            ->map(function ($group) {
                $months = [];
                foreach ($group as $row) {
                    $months[$row->month] = round((float) $row->target_value, 4);
                }
                return $months;
            });

        $spreadsheetData = $kpis->map(function ($kpi) use ($actuals, $targets) {
            $kpiActuals = $actuals->get($kpi->id, []);
            $kpiTargets = $targets->get($kpi->id, []);
            $monthlyData = [];
            $ytdActual = 0;
            $ytdTarget = 0;

            for ($m = 1; $m <= 12; $m++) {
                $actual = $kpiActuals[$m] ?? null;
                $target = $kpiTargets[$m] ?? null;
                $diff = ($actual !== null && $target !== null) ? round($actual - $target, 4) : null;
                $pctDev = ($target && $target != 0 && $diff !== null) ? round($diff / $target * 100, 2) : null;

                $monthlyData[$m] = [
                    'actual' => $actual,
                    'target' => $target,
                    'diff' => $diff,
                    'pct_dev' => $pctDev,
                ];

                if ($actual !== null) $ytdActual += $actual;
                if ($target !== null) $ytdTarget += $target;
            }

            return [
                'id' => $kpi->id,
                'name_de' => $kpi->name_de,
                'name_en' => $kpi->name_en,
                'category' => $kpi->category,
                'unit' => $kpi->unit,
                'direction' => $kpi->direction,
                'formula' => $kpi->formula,
                'months' => $monthlyData,
                'ytd_actual' => round($ytdActual, 4),
                'ytd_target' => round($ytdTarget, 4),
                'ytd_diff' => round($ytdActual - $ytdTarget, 4),
                'ytd_pct_dev' => $ytdTarget != 0 ? round(($ytdActual - $ytdTarget) / $ytdTarget * 100, 2) : null,
            ];
        });

        $categories = KpiDefinition::where('is_template', false)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        $availableYears = KpiValue::selectRaw('EXTRACT(YEAR FROM recorded_at) as yr')
            ->distinct()
            ->pluck('yr')
            ->map(fn ($y) => (int) $y)
            ->sort()
            ->values();

        if (!$availableYears->contains($year)) {
            $availableYears->push($year);
            $availableYears = $availableYears->sort()->values();
        }

        return Inertia::render('Kpis/Spreadsheet', [
            'spreadsheetData' => $spreadsheetData,
            'categories' => $categories,
            'year' => $year,
            'availableYears' => $availableYears,
            'filters' => $request->only(['category', 'year']),
        ]);
    }

    public function storeTargets(Request $request)
    {
        $validated = $request->validate([
            'targets' => ['required', 'array'],
            'targets.*.kpi_definition_id' => ['required', 'exists:kpi_definitions,id'],
            'targets.*.year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'targets.*.month' => ['required', 'integer', 'min:1', 'max:12'],
            'targets.*.target_value' => ['required', 'numeric'],
        ]);

        foreach ($validated['targets'] as $target) {
            KpiMonthlyTarget::updateOrCreate(
                [
                    'kpi_definition_id' => $target['kpi_definition_id'],
                    'year' => $target['year'],
                    'month' => $target['month'],
                ],
                [
                    'target_value' => $target['target_value'],
                ]
            );
        }

        return redirect()->back()->with('success', __('common.success'));
    }

    public function storeActuals(Request $request)
    {
        $validated = $request->validate([
            'actuals' => ['required', 'array'],
            'actuals.*.kpi_definition_id' => ['required', 'exists:kpi_definitions,id'],
            'actuals.*.year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'actuals.*.month' => ['required', 'integer', 'min:1', 'max:12'],
            'actuals.*.value' => ['required', 'numeric'],
        ]);

        foreach ($validated['actuals'] as $entry) {
            $kpi = KpiDefinition::find($entry['kpi_definition_id']);
            $recordedAt = sprintf('%04d-%02d-01', $entry['year'], $entry['month']);

            $existing = KpiValue::where('kpi_definition_id', $entry['kpi_definition_id'])
                ->whereYear('recorded_at', $entry['year'])
                ->whereMonth('recorded_at', $entry['month'])
                ->first();

            $status = $this->calculateStatus($kpi, (float) $entry['value']);

            if ($existing) {
                $existing->update([
                    'value' => $entry['value'],
                    'status' => $status,
                ]);
            } else {
                KpiValue::create([
                    'kpi_definition_id' => $entry['kpi_definition_id'],
                    'value' => $entry['value'],
                    'recorded_at' => $recordedAt,
                    'status' => $status,
                ]);
            }
        }

        return redirect()->back()->with('success', __('common.success'));
    }

    public function generateTargets(Request $request)
    {
        $validated = $request->validate([
            'kpi_definition_id' => ['required', 'exists:kpi_definitions,id'],
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'base_value' => ['required', 'numeric'],
            'growth_rate' => ['required', 'numeric', 'min:-100', 'max:1000'],
            'start_month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $value = (float) $validated['base_value'];
        $rate = (float) $validated['growth_rate'] / 100;

        for ($m = $validated['start_month']; $m <= 12; $m++) {
            KpiMonthlyTarget::updateOrCreate(
                [
                    'kpi_definition_id' => $validated['kpi_definition_id'],
                    'year' => $validated['year'],
                    'month' => $m,
                ],
                [
                    'target_value' => round($value, 4),
                    'growth_rate' => $validated['growth_rate'],
                ]
            );
            $value = $value * (1 + $rate);
        }

        return redirect()->back()->with('success', __('common.success'));
    }

    private function calculateStatus(KpiDefinition $kpi, float $value): string
    {
        if (!$kpi->target_value) {
            return 'on_target';
        }

        if ($kpi->direction === 'higher_better') {
            if ($kpi->critical_threshold && $value <= $kpi->critical_threshold) return 'critical';
            if ($kpi->warning_threshold && $value <= $kpi->warning_threshold) return 'warning';
            return 'on_target';
        }

        if ($kpi->critical_threshold && $value >= $kpi->critical_threshold) return 'critical';
        if ($kpi->warning_threshold && $value >= $kpi->warning_threshold) return 'warning';
        return 'on_target';
    }
}
