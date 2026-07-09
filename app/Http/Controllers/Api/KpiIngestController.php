<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ToolAccess;
use App\Services\KpiIngestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KpiIngestController extends Controller
{
    public function __construct(protected KpiIngestionService $service) {}

    /**
     * POST /api/allocore/kpi/ingest
     *
     * Receives a batch of KPI metrics from an authenticated spoke tool and
     * upserts them into the company's connected KPIs (idempotent per
     * external_ref + metric key).
     */
    public function ingest(Request $request): JsonResponse
    {
        /** @var ToolAccess $access */
        $access = $request->attributes->get('tool_access');

        $validated = $request->validate([
            'source' => 'nullable|string|max:50',
            'external_ref' => 'nullable|string|max:191',
            'recorded_at' => 'nullable|date',
            'metrics' => 'required|array|min:1',
            'metrics.*.key' => 'required|string|max:100',
            'metrics.*.value' => 'required|numeric',
            'metrics.*.scale_max' => 'nullable|numeric',
        ]);

        $ingested = $this->service->ingest(
            $access,
            $validated['metrics'],
            $validated['external_ref'] ?? null,
            $validated['recorded_at'] ?? null,
        );

        return response()->json([
            'ok' => true,
            'tool' => $access->tool,
            'company_id' => $access->company_id,
            'ingested' => $ingested,
        ], 200);
    }
}
