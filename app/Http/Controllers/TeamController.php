<?php

namespace App\Http\Controllers;

use App\Models\KpiDefinition;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function index(): Response
    {
        $this->authorizeManager();

        $companyId = Auth::user()->company_id;

        $members = User::where('company_id', $companyId)
            ->with('assignedKpis:id')
            ->orderByRaw("CASE role WHEN 'owner' THEN 0 WHEN 'manager' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'assigned_kpi_ids' => $u->assignedKpis->pluck('id'),
            ]);

        $kpis = KpiDefinition::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderByDesc('is_connected')
            ->orderBy('name_en')
            ->get()
            ->map(fn (KpiDefinition $k) => [
                'id' => $k->id,
                'name_de' => $k->name_de,
                'name_en' => $k->name_en,
                'source' => $k->source,
            ]);

        return Inertia::render('Team/Index', [
            'members' => $members,
            'kpis' => $kpis,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManager();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email',
            'role' => 'required|in:manager,member',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'company_id' => Auth::user()->company_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', __('User created.'));
    }

    public function assign(Request $request, User $user): RedirectResponse
    {
        $this->authorizeManager();
        abort_unless($user->company_id === Auth::user()->company_id, 403);

        $validated = $request->validate([
            'kpi_ids' => 'array',
            'kpi_ids.*' => 'integer|exists:kpi_definitions,id',
        ]);

        // Only allow KPIs that belong to this company.
        $companyKpiIds = KpiDefinition::where('company_id', Auth::user()->company_id)
            ->whereIn('id', $validated['kpi_ids'] ?? [])
            ->pluck('id')
            ->all();

        $user->assignedKpis()->sync($companyKpiIds);

        return back()->with('success', __('KPI assignments updated.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorizeManager();
        abort_unless($user->company_id === Auth::user()->company_id, 403);
        abort_if($user->isOwner(), 403, 'Cannot remove the company owner.');
        abort_if($user->id === Auth::id(), 403);

        $user->delete();

        return back()->with('success', __('User removed.'));
    }

    protected function authorizeManager(): void
    {
        abort_unless(Auth::user()?->canManageCompany(), 403);
    }
}
