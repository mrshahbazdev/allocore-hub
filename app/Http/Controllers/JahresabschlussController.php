<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\JahresabschlussInput;
use App\Services\KennzahlenEngine;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class JahresabschlussController extends Controller
{
    public function index()
    {
        $company = Auth::user()->currentCompany();

        $analyses = Analysis::with('company')
            ->when($company, fn ($query) => $query->where('company_id', $company->id))
            ->where('type', 'jahresabschluss')
            ->latest()
            ->paginate(10);

        return view('jahresabschluss.index', compact('analyses'));
    }

    public function create()
    {
        $companies = Auth::user()->companies;

        return view('jahresabschluss.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $companyIds = Auth::user()->companies->pluck('id')->all();

        $request->validate([
            'company_id' => ['required', Rule::in($companyIds)],
            'name' => 'required|string|max:255',
            'years' => 'required|array|min:1|max:3',
            'years.*.year_label' => 'required|string|max:10',
            'years.*.revenue' => 'nullable|numeric',
            'years.*.ebit' => 'nullable|numeric',
            'years.*.total_assets' => 'nullable|numeric',
            'years.*.equity' => 'nullable|numeric',
        ]);

        $analysis = Analysis::create([
            'company_id' => $request->company_id,
            'user_id' => Auth::id(),
            'type' => 'jahresabschluss',
            'name' => $request->name,
            'status' => 'draft',
        ]);

        // Save each year's data
        foreach ($request->years as $order => $yearData) {
            JahresabschlussInput::create(array_merge($yearData, [
                'analysis_id' => $analysis->id,
                'year_order' => $order + 1,
            ]));
        }

        // Run KennzahlenEngine
        $years = $analysis->jahresabschlussInputs;
        $engine = new KennzahlenEngine($years);
        $engine->calculateAndSave($analysis);

        return redirect()->route('jahresabschluss.show', $analysis)
            ->with('success', 'Jahresabschluss-Analyse erfolgreich erstellt.');
    }

    public function show(Analysis $jahresabschluss)
    {
        $this->authorize('view', $jahresabschluss);
        $jahresabschluss->load(['company', 'jahresabschlussInputs', 'kpiResults']);

        $years = $jahresabschluss->jahresabschlussInputs;
        $engine = new KennzahlenEngine($years);
        $bericht = $engine->generateBericht();

        return view('jahresabschluss.show', [
            'analysis' => $jahresabschluss,
            'bericht' => $bericht,
        ]);
    }

    public function destroy(Analysis $jahresabschluss)
    {
        $this->authorize('delete', $jahresabschluss);
        $jahresabschluss->delete();

        return redirect()->route('jahresabschluss.index')
            ->with('success', 'Analyse gelöscht.');
    }

    public function exportPdf(Analysis $jahresabschluss)
    {
        $this->authorize('view', $jahresabschluss);
        $jahresabschluss->load(['company', 'jahresabschlussInputs', 'kpiResults']);

        $years = $jahresabschluss->jahresabschlussInputs;
        $engine = new KennzahlenEngine($years);
        $bericht = $engine->generateBericht();

        $pdf = Pdf::loadView('jahresabschluss.pdf', [
            'analysis' => $jahresabschluss,
            'bericht' => $bericht,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('jahresabschluss-'.$jahresabschluss->id.'.pdf');
    }
}
