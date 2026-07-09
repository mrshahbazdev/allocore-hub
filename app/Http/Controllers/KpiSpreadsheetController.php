<?php

namespace App\Http\Controllers;

use App\Models\KpiDefinition;
use App\Models\KpiMonthlyTarget;
use App\Models\KpiValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class KpiSpreadsheetController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $year = (int) ($request->input('year') ?: date('Y'));
        $categoryFilter = $request->input('category');

        $query = KpiDefinition::visibleTo($user)
            ->where('is_template', false)
            ->where('is_active', true);

        if ($categoryFilter) {
            $query->where('category', $categoryFilter);
        }

        $kpis = $query->orderBy('category')->orderBy('name_de')->get();
        $kpiIds = $kpis->pluck('id');

        $actuals = $this->monthlyActuals($kpiIds, $year);

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

                if ($actual !== null) {
                    $ytdActual += $actual;
                }
                if ($target !== null) {
                    $ytdTarget += $target;
                }
            }

            return [
                'id' => $kpi->id,
                'name_de' => $kpi->name_de,
                'name_en' => $kpi->name_en,
                'category' => $kpi->category,
                'unit' => $kpi->unit,
                'direction' => $kpi->direction,
                'formula' => $kpi->formula,
                'is_connected' => $kpi->is_connected,
                'months' => $monthlyData,
                'ytd_actual' => round($ytdActual, 4),
                'ytd_target' => round($ytdTarget, 4),
                'ytd_diff' => round($ytdActual - $ytdTarget, 4),
                'ytd_pct_dev' => $ytdTarget != 0 ? round(($ytdActual - $ytdTarget) / $ytdTarget * 100, 2) : null,
            ];
        });

        $categories = KpiDefinition::visibleTo($user)
            ->where('is_template', false)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        $availableYears = KpiValue::whereIn('kpi_definition_id', $kpiIds)
            ->get(['recorded_at'])
            ->map(fn ($v) => (int) $v->recorded_at->format('Y'))
            ->unique()
            ->sort()
            ->values();

        if (! $availableYears->contains($year)) {
            $availableYears->push($year);
            $availableYears = $availableYears->sort()->values();
        }

        return Inertia::render('Kpis/Spreadsheet', [
            'spreadsheetData' => $spreadsheetData,
            'categories' => $categories,
            'year' => $year,
            'availableYears' => $availableYears,
            'canManage' => $user->canManageCompany(),
            'filters' => $request->only(['category', 'year']),
        ]);
    }

    public function storeTargets(Request $request)
    {
        $this->authorizeManager();

        $validated = $request->validate([
            'targets' => ['required', 'array'],
            'targets.*.kpi_definition_id' => ['required', 'integer'],
            'targets.*.year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'targets.*.month' => ['required', 'integer', 'min:1', 'max:12'],
            'targets.*.target_value' => ['required', 'numeric'],
        ]);

        foreach ($validated['targets'] as $target) {
            $this->assertCompanyKpi($target['kpi_definition_id']);

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
            'actuals.*.kpi_definition_id' => ['required', 'integer'],
            'actuals.*.year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'actuals.*.month' => ['required', 'integer', 'min:1', 'max:12'],
            'actuals.*.value' => ['required', 'numeric'],
        ]);

        foreach ($validated['actuals'] as $entry) {
            $kpi = $this->assertVisibleKpi($entry['kpi_definition_id']);
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
                    'source' => 'manual',
                    'recorded_by' => Auth::id(),
                ]);
            }
        }

        return redirect()->back()->with('success', __('common.success'));
    }

    public function generateTargets(Request $request)
    {
        $this->authorizeManager();

        $validated = $request->validate([
            'kpi_definition_id' => ['required', 'integer'],
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'base_value' => ['required', 'numeric'],
            'growth_rate' => ['required', 'numeric', 'min:-100', 'max:1000'],
            'start_month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $this->assertCompanyKpi($validated['kpi_definition_id']);

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

    public function export(Request $request)
    {
        $user = Auth::user();
        $year = (int) ($request->input('year') ?: date('Y'));

        $kpis = KpiDefinition::visibleTo($user)
            ->where('is_template', false)
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name_de')
            ->get();

        $kpiIds = $kpis->pluck('id');
        $actuals = $this->monthlyActuals($kpiIds, $year);

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

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $headers = ['KPI', 'Category', 'Unit', 'Type'];
        foreach ($months as $m) {
            $headers[] = $m;
        }
        $headers[] = 'YTD';

        $rows = [];
        foreach ($kpis as $kpi) {
            $kpiActuals = $actuals->get($kpi->id, []);
            $kpiTargets = $targets->get($kpi->id, []);

            $actualRow = [$kpi->name_en, $kpi->category, $kpi->unit, 'Actual'];
            $targetRow = [$kpi->name_en, $kpi->category, $kpi->unit, 'Target'];
            $ytdA = 0;
            $ytdT = 0;

            for ($m = 1; $m <= 12; $m++) {
                $a = $kpiActuals[$m] ?? '';
                $t = $kpiTargets[$m] ?? '';
                $actualRow[] = $a === '' ? '' : round($a, 2);
                $targetRow[] = $t === '' ? '' : round($t, 2);
                if (is_numeric($a)) {
                    $ytdA += $a;
                }
                if (is_numeric($t)) {
                    $ytdT += $t;
                }
            }

            $actualRow[] = round($ytdA, 2);
            $targetRow[] = round($ytdT, 2);

            $rows[] = $actualRow;
            $rows[] = $targetRow;
        }

        $filename = "kpi-spreadsheet-{$year}.csv";

        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Average actual value per month, grouped per KPI. DB-agnostic.
     */
    private function monthlyActuals($kpiIds, int $year)
    {
        return KpiValue::whereIn('kpi_definition_id', $kpiIds)
            ->whereYear('recorded_at', $year)
            ->get(['kpi_definition_id', 'recorded_at', 'value'])
            ->groupBy('kpi_definition_id')
            ->map(function ($group) {
                return $group->groupBy(fn ($v) => (int) $v->recorded_at->format('n'))
                    ->map(fn ($rows) => round($rows->avg('value'), 4))
                    ->all();
            });
    }

    private function calculateStatus(KpiDefinition $kpi, float $value): string
    {
        if ($kpi->direction === 'higher_better') {
            if ($kpi->critical_threshold && $value <= $kpi->critical_threshold) {
                return 'critical';
            }
            if ($kpi->warning_threshold && $value <= $kpi->warning_threshold) {
                return 'warning';
            }

            return 'on_target';
        }

        if ($kpi->critical_threshold && $value >= $kpi->critical_threshold) {
            return 'critical';
        }
        if ($kpi->warning_threshold && $value >= $kpi->warning_threshold) {
            return 'warning';
        }

        return 'on_target';
    }

    protected function authorizeManager(): void
    {
        abort_unless(Auth::user()?->canManageCompany(), 403);
    }

    protected function assertCompanyKpi(int $kpiId): KpiDefinition
    {
        $kpi = KpiDefinition::where('company_id', Auth::user()->company_id)->findOrFail($kpiId);

        return $kpi;
    }

    protected function assertVisibleKpi(int $kpiId): KpiDefinition
    {
        $kpi = KpiDefinition::visibleTo(Auth::user())->findOrFail($kpiId);

        return $kpi;
    }
}
