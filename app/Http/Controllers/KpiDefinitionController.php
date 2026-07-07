<?php

namespace App\Http\Controllers;

use App\Models\KpiDefinition;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KpiDefinitionController extends Controller
{
    public function index(Request $request)
    {
        $query = KpiDefinition::with('latestValue')
            ->where('is_template', false);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_de', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%");
            });
        }

        $kpis = $query->orderBy('category')->orderBy('name_de')->paginate(20);

        return Inertia::render('Kpis/Index', [
            'kpis' => $kpis,
            'filters' => $request->only(['category', 'search']),
            'categories' => KpiDefinition::where('is_template', false)
                ->distinct()
                ->pluck('category')
                ->filter()
                ->values(),
        ]);
    }

    public function create()
    {
        $templates = KpiDefinition::where('is_template', true)->get();

        return Inertia::render('Kpis/Create', [
            'templates' => $templates,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_de' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'description_de' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'formula' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:50'],
            'target_value' => ['nullable', 'numeric'],
            'warning_threshold' => ['nullable', 'numeric'],
            'critical_threshold' => ['nullable', 'numeric'],
            'frequency' => ['required', 'in:daily,weekly,monthly,quarterly,yearly'],
            'direction' => ['required', 'in:higher_better,lower_better'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        KpiDefinition::create($validated);

        return redirect('/kpis')->with('success', __('common.success'));
    }

    public function show(KpiDefinition $kpi)
    {
        $kpi->load(['values' => function ($q) {
            $q->orderBy('recorded_at', 'desc')->limit(90);
        }]);

        return Inertia::render('Kpis/Show', [
            'kpi' => $kpi,
            'values' => $kpi->values->map(function ($v) {
                return [
                    'id' => $v->id,
                    'value' => (float) $v->value,
                    'recorded_at' => $v->recorded_at->format('Y-m-d'),
                    'status' => $v->status,
                    'notes' => $v->notes,
                ];
            }),
        ]);
    }

    public function edit(KpiDefinition $kpi)
    {
        return Inertia::render('Kpis/Edit', [
            'kpi' => $kpi,
        ]);
    }

    public function update(Request $request, KpiDefinition $kpi)
    {
        $validated = $request->validate([
            'name_de' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'description_de' => ['nullable', 'string'],
            'description_en' => ['nullable', 'string'],
            'formula' => ['nullable', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:50'],
            'target_value' => ['nullable', 'numeric'],
            'warning_threshold' => ['nullable', 'numeric'],
            'critical_threshold' => ['nullable', 'numeric'],
            'frequency' => ['required', 'in:daily,weekly,monthly,quarterly,yearly'],
            'direction' => ['required', 'in:higher_better,lower_better'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        $kpi->update($validated);

        return redirect("/kpis/{$kpi->id}")->with('success', __('common.success'));
    }

    public function destroy(KpiDefinition $kpi)
    {
        $kpi->delete();
        return redirect('/kpis')->with('success', __('common.success'));
    }

    public function catalog()
    {
        $templates = KpiDefinition::where('is_template', true)
            ->orderBy('category')
            ->orderBy('name_de')
            ->get();

        return Inertia::render('Kpis/Catalog', [
            'templates' => $templates,
        ]);
    }

    public function useTemplate(Request $request, KpiDefinition $template)
    {
        $validated = $request->validate([
            'target_value' => ['nullable', 'numeric'],
            'warning_threshold' => ['nullable', 'numeric'],
            'critical_threshold' => ['nullable', 'numeric'],
        ]);

        $kpi = $template->replicate();
        $kpi->is_template = false;
        $kpi->target_value = $validated['target_value'] ?? $template->target_value;
        $kpi->warning_threshold = $validated['warning_threshold'] ?? $template->warning_threshold;
        $kpi->critical_threshold = $validated['critical_threshold'] ?? $template->critical_threshold;
        $kpi->save();

        return redirect("/kpis/{$kpi->id}")->with('success', __('common.success'));
    }
}
