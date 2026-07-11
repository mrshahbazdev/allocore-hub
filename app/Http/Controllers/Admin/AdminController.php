<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Analysis;
use App\Models\Company;
use App\Models\KpiThreshold;
use App\Models\Setting;
use App\Models\User;
use App\Services\InvoiceMakerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    // ─── Admin Dashboard ──────────────────────────────────────────────
    public function index()
    {
        $stats = [
            'users' => User::count(),
            'analyses' => Analysis::count(),
            'companies' => Company::count(),
            'gmbh' => Analysis::where('type', 'gmbh')->count(),
            'jahrabs' => Analysis::where('type', 'jahresabschluss')->count(),
            'immobilien' => Analysis::where('type', 'immobilien')->count(),
            'complete' => Analysis::where('status', 'complete')->count(),
        ];

        $recentUsers = User::with('roles')->latest()->take(8)->get();

        $topAnalyses = Analysis::with(['company', 'user'])
            ->whereNotNull('total_score')
            ->orderByDesc('total_score')
            ->take(5)
            ->get();

        return view('admin.index', compact('stats', 'recentUsers', 'topAnalyses'));
    }

    // ─── User Management ──────────────────────────────────────────────
    public function users()
    {
        $users = User::with('roles')
            ->withCount('analyses')
            ->latest()
            ->paginate(20);

        $roles = Role::all();

        return view('admin.users', compact('users', 'roles'));
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $user->syncRoles([$request->role]);

        return back()->with('success', "Rolle von {$user->name} auf {$request->role} geändert.");
    }

    // ─── KPI Threshold Management ─────────────────────────────────────
    public function thresholds()
    {
        $thresholds = KpiThreshold::orderBy('tool')->orderBy('kpi_code')->get();
        $grouped = $thresholds->groupBy('tool');

        return view('admin.thresholds', compact('grouped'));
    }

    public function updateThreshold(Request $request, KpiThreshold $threshold)
    {
        $request->validate([
            'green_min' => 'nullable|numeric',
            'yellow_min' => 'nullable|numeric',
            'green_max' => 'nullable|numeric',
            'yellow_max' => 'nullable|numeric',
            'weight' => 'nullable|numeric|min:0|max:100',
            'lower_is_better' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $threshold->update($request->only([
            'green_min', 'yellow_min', 'green_max', 'yellow_max',
            'weight', 'lower_is_better', 'is_active',
        ]));

        return back()->with('success', "KPI-Schwellwert für {$threshold->kpi_name} aktualisiert.");
    }

    // ─── Invoice Maker Integration ──────────────────────────────────────
    public function invoiceMaker()
    {
        $settings = [
            'api_key' => Setting::get('invoicemaker.api_key', ''),
            'base_url' => Setting::get('invoicemaker.base_url', config('invoicemaker.base_url', 'https://invoice.allocore.de')),
        ];

        return view('admin.invoicemaker', compact('settings'));
    }

    public function saveInvoiceMaker(Request $request)
    {
        $request->validate([
            'api_key' => 'nullable|string|max:255',
            'base_url' => 'nullable|url|max:255',
        ]);

        Setting::set('invoicemaker.api_key', $request->input('api_key'));
        Setting::set('invoicemaker.base_url', $request->input('base_url'));

        return back()->with('success', 'Invoice Maker-Einstellungen gespeichert.');
    }

    public function testInvoiceMaker()
    {
        $service = app(InvoiceMakerService::class);

        if (! $service->isConfigured()) {
            return back()->with('error', 'Invoice Maker ist nicht konfiguriert. Bitte API-Key und URL eingeben.');
        }

        $apiKey = Setting::get('invoicemaker.api_key') ?: config('invoicemaker.api_key', '');
        $baseUrl = rtrim(Setting::get('invoicemaker.base_url') ?: config('invoicemaker.base_url', ''), '/');

        try {
            $response = Http::timeout(10)
                ->withHeaders(['X-Allocore-Api-Key' => $apiKey])
                ->get("{$baseUrl}/api/allocore/invoices/by-order/test-ping");

            if ($response->status() === 404) {
                return back()->with('success', 'Verbindung erfolgreich! Invoice Maker API ist erreichbar.');
            }

            if ($response->status() === 401) {
                return back()->with('error', 'API-Key ungültig. Bitte den gleichen Key wie in Invoice Maker verwenden.');
            }

            return back()->with('success', 'Verbindung erfolgreich! (HTTP '.$response->status().')');
        } catch (\Exception $e) {
            return back()->with('error', 'Verbindung fehlgeschlagen: '.$e->getMessage());
        }
    }
}
