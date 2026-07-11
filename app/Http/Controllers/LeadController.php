<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $company = Auth::user()->currentCompany();
        $query = Lead::when($company, fn ($q) => $q->where('company_id', $company->id));

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('company_name', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $leads = $query->with('company')->latest()->paginate(20)->withQueryString();

        $stats = [
            'total' => Lead::when($company, fn ($q) => $q->where('company_id', $company->id))->count(),
            'new' => Lead::when($company, fn ($q) => $q->where('company_id', $company->id))->where('status', 'new')->count(),
            'contacted' => Lead::when($company, fn ($q) => $q->where('company_id', $company->id))->where('status', 'contacted')->count(),
            'qualified' => Lead::when($company, fn ($q) => $q->where('company_id', $company->id))->where('status', 'qualified')->count(),
            'transferred' => Lead::when($company, fn ($q) => $q->where('company_id', $company->id))->where('transferred_to_leados', true)->count(),
        ];

        return view('leads.index', compact('leads', 'stats'));
    }

    public function create()
    {
        $companies = Auth::user()->companies->sortBy('name');

        return view('leads.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $companyIds = Auth::user()->companies->pluck('id')->all();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company_id' => ['nullable', Rule::in($companyIds)],
            'company_name' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'linkedin' => 'nullable|url|max:500',
            'website' => 'nullable|url|max:500',
            'source' => 'nullable|string|max:100',
            'status' => 'nullable|in:new,contacted,qualified,proposal,won,lost',
            'priority' => 'nullable|in:low,medium,high,critical',
            'industry' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:5000',
        ]);

        $validated['user_id'] = Auth::id();

        Lead::create($validated);

        return redirect()->route('leads.index')->with('success', 'Lead erfolgreich erstellt.');
    }

    public function show(Lead $lead)
    {
        $this->authorise($lead);
        $lead->load('company', 'paypalTransactions');

        return view('leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        $this->authorise($lead);
        $companies = Auth::user()->companies->sortBy('name');

        return view('leads.edit', compact('lead', 'companies'));
    }

    public function update(Request $request, Lead $lead)
    {
        $this->authorise($lead);

        $companyIds = Auth::user()->companies->pluck('id')->all();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company_id' => ['nullable', Rule::in($companyIds)],
            'company_name' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'linkedin' => 'nullable|url|max:500',
            'website' => 'nullable|url|max:500',
            'source' => 'nullable|string|max:100',
            'status' => 'nullable|in:new,contacted,qualified,proposal,won,lost',
            'priority' => 'nullable|in:low,medium,high,critical',
            'industry' => 'nullable|string|max:255',
            'budget' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:5000',
        ]);

        $lead->update($validated);

        return redirect()->route('leads.show', $lead)->with('success', 'Lead aktualisiert.');
    }

    public function destroy(Lead $lead)
    {
        $this->authorise($lead);
        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'Lead gelöscht.');
    }

    public function transferToLeadOs(Request $request)
    {
        $request->validate([
            'lead_ids' => 'required|array|min:1',
            'lead_ids.*' => 'exists:leads,id',
            'leados_api_url' => 'required|url',
            'leados_token' => 'required|string',
        ]);

        $company = Auth::user()->currentCompany();

        $leads = Lead::when($company, fn ($q) => $q->where('company_id', $company->id))
            ->whereIn('id', $request->lead_ids)
            ->where('transferred_to_leados', false)
            ->get();

        if ($leads->isEmpty()) {
            return back()->with('error', 'Keine übertragbaren Leads ausgewählt.');
        }

        $transferred = 0;
        $errors = [];

        foreach ($leads as $lead) {
            try {
                $response = Http::withToken($request->leados_token)
                    ->timeout(15)
                    ->post(rtrim($request->leados_api_url, '/').'/api/leads/import', [
                        'name' => $lead->name,
                        'email' => $lead->email,
                        'company' => $lead->company_name ?? $lead->company?->name,
                        'position' => $lead->position,
                        'linkedin' => $lead->linkedin,
                    ]);

                if ($response->successful()) {
                    $lead->update([
                        'transferred_to_leados' => true,
                        'transferred_at' => now(),
                    ]);
                    $transferred++;
                } else {
                    $errors[] = "{$lead->name}: ".($response->json('message') ?? 'API-Fehler');
                }
            } catch (\Exception $e) {
                $errors[] = "{$lead->name}: Verbindungsfehler";
            }
        }

        $message = "{$transferred} Lead(s) erfolgreich an LeadOS übertragen.";
        if (! empty($errors)) {
            $message .= ' Fehler: '.implode(', ', $errors);
        }

        return back()->with($errors ? 'error' : 'success', $message);
    }

    public function exportCsv(Request $request)
    {
        $company = Auth::user()->currentCompany();

        $leads = Lead::when($company, fn ($q) => $q->where('company_id', $company->id))
            ->with('company')
            ->latest()
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads-export-'.date('Y-m-d').'.csv"',
        ];

        $callback = function () use ($leads) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'E-Mail', 'Telefon', 'Unternehmen', 'Position', 'Status', 'Priorität', 'Quelle', 'Budget', 'LeadOS', 'Erstellt']);

            foreach ($leads as $lead) {
                fputcsv($out, [
                    $lead->name,
                    $lead->email,
                    $lead->phone,
                    $lead->company_name ?? $lead->company?->name ?? '',
                    $lead->position,
                    $lead->status,
                    $lead->priority,
                    $lead->source,
                    $lead->budget,
                    $lead->transferred_to_leados ? 'Ja' : 'Nein',
                    $lead->created_at?->format('d.m.Y'),
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function authorise(Lead $lead): void
    {
        $user = Auth::user();
        $company = $user->currentCompany();

        if ($lead->company_id) {
            abort_unless($lead->company?->hasUser($user), 403);

            return;
        }

        abort_unless($lead->user_id === $user->id, 403);
    }
}
