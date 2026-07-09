<?php

namespace App\Http\Controllers;

use App\Models\ToolAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ToolController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeManager();

        $companyId = Auth::user()->company_id;

        $connected = ToolAccess::where('company_id', $companyId)
            ->orderBy('name')
            ->get()
            ->keyBy('tool');

        $tools = collect(ToolAccess::CATALOG)->map(function ($label, $slug) use ($connected) {
            $access = $connected->get($slug);

            return [
                'tool' => $slug,
                'label' => $label,
                'connected' => (bool) $access,
                'id' => $access?->id,
                'name' => $access?->name ?? $label,
                'base_url' => $access?->base_url,
                'enabled' => $access?->enabled ?? false,
                'status' => $access?->status,
                'last_synced_at' => $access?->last_synced_at?->toDateTimeString(),
            ];
        })->values();

        return Inertia::render('Tools/Index', [
            'tools' => $tools,
            'ingestUrl' => rtrim(config('allocore.hub_url') ?: config('app.url'), '/').'/api/allocore/kpi/ingest',
            'revealedKey' => $request->session()->get('revealed_key'),
            'revealedTool' => $request->session()->get('revealed_tool'),
        ]);
    }

    public function connect(Request $request): RedirectResponse
    {
        $this->authorizeManager();

        $validated = $request->validate([
            'tool' => 'required|string|in:'.implode(',', array_keys(ToolAccess::CATALOG)),
            'base_url' => 'nullable|url|max:255',
        ]);

        $companyId = Auth::user()->company_id;
        $key = ToolAccess::generateKey();

        $access = ToolAccess::updateOrCreate(
            ['company_id' => $companyId, 'tool' => $validated['tool']],
            [
                'name' => ToolAccess::CATALOG[$validated['tool']],
                'base_url' => $validated['base_url'] ?? null,
                'api_key' => $key,
                'enabled' => true,
                'status' => 'pending',
            ]
        );

        return back()
            ->with('revealed_key', $key)
            ->with('revealed_tool', $access->tool)
            ->with('success', __('Tool connected. Copy the API key now — it is shown only once.'));
    }

    public function regenerate(ToolAccess $tool): RedirectResponse
    {
        $this->authorizeManager();
        $this->authorizeOwnership($tool);

        $key = ToolAccess::generateKey();
        $tool->update(['api_key' => $key, 'status' => 'pending']);

        return back()
            ->with('revealed_key', $key)
            ->with('revealed_tool', $tool->tool)
            ->with('success', __('API key regenerated.'));
    }

    public function toggle(ToolAccess $tool): RedirectResponse
    {
        $this->authorizeManager();
        $this->authorizeOwnership($tool);

        $tool->update(['enabled' => ! $tool->enabled]);

        return back()->with('success', __('Tool updated.'));
    }

    public function destroy(ToolAccess $tool): RedirectResponse
    {
        $this->authorizeManager();
        $this->authorizeOwnership($tool);

        $tool->delete();

        return back()->with('success', __('Tool disconnected.'));
    }

    protected function authorizeManager(): void
    {
        abort_unless(Auth::user()?->canManageCompany(), 403);
    }

    protected function authorizeOwnership(ToolAccess $tool): void
    {
        abort_unless($tool->company_id === Auth::user()->company_id, 403);
    }
}
