<?php

namespace App\Http\Controllers;

use App\Models\Analysis;
use App\Models\Lead;
use App\Models\PaypalTransaction;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $company = $user->currentCompany();

        $stats = [
            'companies' => $user->companies()->count(),
            'gmbh' => $company ? Analysis::where('company_id', $company->id)->where('type', 'gmbh')->count() : 0,
            'jahresabschluss' => $company ? Analysis::where('company_id', $company->id)->where('type', 'jahresabschluss')->count() : 0,
            'immobilien' => $company ? Analysis::where('company_id', $company->id)->where('type', 'immobilien')->count() : 0,
            'leads' => $company ? Lead::where('company_id', $company->id)->count() : 0,
            'paypal_revenue' => $company ? PaypalTransaction::where('user_id', $user->id)->where('status', 'completed')->sum('amount') : 0,
        ];

        $recentAnalyses = $company
            ? Analysis::with('company')->where('company_id', $company->id)->whereNotNull('total_score')->latest()->take(8)->get()
            : collect();

        $companies = $user->companies()->withCount('analyses')->latest()->take(5)->get();

        return view('dashboard', compact('stats', 'recentAnalyses', 'companies'));
    }
}
