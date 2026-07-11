<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Auth::user()->companies()
            ->withCount('analyses')
            ->latest('company_user.created_at')
            ->paginate(12);

        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        return view('companies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
            'country' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        $company = Company::create(array_merge(
            $request->only(['name', 'industry', 'currency', 'country', 'description']),
            [
                'user_id' => Auth::id(),
                'slug' => Str::slug($request->name).'-'.Str::random(6),
            ]
        ));

        Auth::user()->companies()->attach($company->id, [
            'role' => Company::ROLE_OWNER,
            'is_default' => true,
        ]);

        app(SubscriptionService::class)->createTrial($company);

        $request->user()->setCurrentCompany($company);

        return redirect()->route('companies.index')
            ->with('success', 'Unternehmen erfolgreich angelegt.');
    }

    public function show(Company $company)
    {
        $this->authorize('view', $company);

        Auth::user()->setCurrentCompany($company);

        $analyses = $company->analyses()->with('kpiResults')->latest()->get();

        return view('companies.show', compact('company', 'analyses'));
    }

    public function edit(Company $company)
    {
        $this->authorize('update', $company);

        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $this->authorize('update', $company);

        $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:100',
            'currency' => 'nullable|string|max:10',
        ]);

        $company->update($request->only(['name', 'industry', 'currency', 'country', 'description']));

        return redirect()->route('companies.show', $company)
            ->with('success', 'Unternehmen aktualisiert.');
    }

    public function destroy(Company $company)
    {
        $this->authorize('delete', $company);

        $company->delete();

        return redirect()->route('companies.index')
            ->with('success', 'Unternehmen gelöscht.');
    }

    public function switch(Company $company)
    {
        $this->authorize('view', $company);

        Auth::user()->setCurrentCompany($company);

        return redirect()->intended(route('dashboard'));
    }
}
