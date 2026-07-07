<?php

namespace App\Http\Controllers;

use App\Models\KpiDefinition;
use App\Models\KpiValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KpiValueController extends Controller
{
    public function store(Request $request, KpiDefinition $kpi)
    {
        $validated = $request->validate([
            'value' => ['required', 'numeric'],
            'recorded_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $status = $this->calculateStatus($kpi, (float) $validated['value']);

        $kpi->values()->create([
            'value' => $validated['value'],
            'recorded_at' => $validated['recorded_at'],
            'notes' => $validated['notes'] ?? null,
            'recorded_by' => Auth::id(),
            'status' => $status,
        ]);

        return redirect()->back()->with('success', __('common.success'));
    }

    public function import(Request $request, KpiDefinition $kpi)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle);
        $imported = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) continue;

            $date = trim($row[0]);
            $value = trim($row[1]);
            $notes = isset($row[2]) ? trim($row[2]) : null;

            if (!is_numeric($value)) continue;

            try {
                $recordedAt = \Carbon\Carbon::parse($date)->format('Y-m-d');
            } catch (\Exception $e) {
                continue;
            }

            $status = $this->calculateStatus($kpi, (float) $value);

            KpiValue::updateOrCreate(
                [
                    'kpi_definition_id' => $kpi->id,
                    'recorded_at' => $recordedAt,
                ],
                [
                    'value' => $value,
                    'notes' => $notes,
                    'recorded_by' => Auth::id(),
                    'status' => $status,
                ]
            );

            $imported++;
        }

        fclose($handle);

        return redirect()->back()->with('success', "{$imported} values imported");
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
