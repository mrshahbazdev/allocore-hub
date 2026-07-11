<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Analysis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiAnalysisController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $company = $request->user()->currentCompany();

        $query = Analysis::with(['company', 'kpiResults'])
            ->when($company, fn ($q) => $q->where('company_id', $company->id))
            ->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $analyses = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $analyses->items(),
            'meta' => [
                'current_page' => $analyses->currentPage(),
                'last_page' => $analyses->lastPage(),
                'total' => $analyses->total(),
                'per_page' => $analyses->perPage(),
            ],
        ]);
    }

    public function show(Request $request, Analysis $analysis): JsonResponse
    {
        if (! $analysis->company?->hasUser($request->user()) && ! $request->user()->hasRole('Admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $analysis->load(['company', 'kpiResults', 'gmbhInput', 'jahresabschlussInputs', 'immobilienInput']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $analysis->id,
                'name' => $analysis->name,
                'type' => $analysis->type,
                'type_label' => $analysis->typeLabel(),
                'status' => $analysis->status,
                'total_score' => $analysis->total_score,
                'score_color' => $analysis->scoreColor(),
                'recommendation' => $analysis->recommendation,
                'company' => $analysis->company,
                'kpi_results' => $analysis->kpiResults,
                'created_at' => $analysis->created_at->toIso8601String(),
            ],
        ]);
    }

    public function destroy(Request $request, Analysis $analysis): JsonResponse
    {
        if (! $analysis->company?->isAdmin($request->user()) && ! $request->user()->hasRole('Admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $analysis->delete();

        return response()->json(['success' => true, 'message' => 'Analyse gelöscht.']);
    }

    public function stats(Request $request): JsonResponse
    {
        $company = $request->user()->currentCompany();
        $query = $company ? Analysis::where('company_id', $company->id) : Analysis::where('user_id', $request->user()->id);

        return response()->json([
            'success' => true,
            'data' => [
                'total_analyses' => (clone $query)->count(),
                'complete' => (clone $query)->where('status', 'complete')->count(),
                'gmbh' => (clone $query)->where('type', 'gmbh')->count(),
                'jahresabschluss' => (clone $query)->where('type', 'jahresabschluss')->count(),
                'immobilien' => (clone $query)->where('type', 'immobilien')->count(),
                'avg_score' => round((clone $query)->whereNotNull('total_score')->avg('total_score'), 1),
                'top_analysis' => (clone $query)->orderByDesc('total_score')->first()?->only(['id', 'name', 'total_score', 'type']),
            ],
        ]);
    }
}
