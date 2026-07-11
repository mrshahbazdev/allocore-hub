<?php

namespace App\Services;

use App\Models\Audit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AllocoreKpiReporter
{
    /**
     * Map of the official 5-pillar template names to Allocore metric keys.
     */
    protected const PILLAR_KEYS = [
        'Umsatz' => 'audit.umsatz',
        'Gewinn' => 'audit.gewinn',
        'Ordnung' => 'audit.ordnung',
        'Einfluss' => 'audit.einfluss',
        'Vermächtnis' => 'audit.vermaechtnis',
    ];

    /**
     * Push a completed audit's pillar scores (and overall Enterprise Readiness)
     * to the Allocore Hub. Failures are logged and never interrupt the audit.
     */
    public function report(Audit $audit): bool
    {
        [$hubUrl, $apiKey] = $this->connectionFor($audit);

        if (! $hubUrl || ! $apiKey) {
            return false;
        }

        $metrics = $this->buildMetrics($audit);

        if (empty($metrics)) {
            return false;
        }

        try {
            $response = Http::timeout((int) config('allocore.timeout', 5))
                ->withHeaders(['X-Allocore-Api-Key' => $apiKey])
                ->acceptJson()
                ->post($this->ingestUrl($hubUrl), [
                    'source' => config('allocore.source', 'audit'),
                    'external_ref' => 'audit-'.$audit->id,
                    'recorded_at' => optional($audit->updated_at)->toDateString() ?? now()->toDateString(),
                    'metrics' => $metrics,
                ]);

            if ($response->failed()) {
                Log::warning('Allocore KPI push failed', [
                    'audit_id' => $audit->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                $this->markOrganization($audit, 'error');

                return false;
            }

            $this->markOrganization($audit, 'connected');

            return true;
        } catch (\Throwable $e) {
            Log::warning('Allocore KPI push error', [
                'audit_id' => $audit->id,
                'error' => $e->getMessage(),
            ]);

            $this->markOrganization($audit, 'error');

            return false;
        }
    }

    /**
     * Resolve the hub URL + API key for an audit.
     *
     * API reporting is disabled by default; when enabled the global config
     * values are used.
     *
     * @return array{0: ?string, 1: ?string}
     */
    protected function connectionFor(Audit $audit): array
    {
        if (! (bool) config('allocore.enabled', false)) {
            return [null, null];
        }

        return [config('allocore.hub_url'), config('allocore.api_key')];
    }

    protected function markOrganization(Audit $audit, string $status): void
    {
        // No-op while API reporting is disabled.
    }

    public function configured(): bool
    {
        return (bool) config('allocore.enabled', true)
            && filled(config('allocore.hub_url'))
            && filled(config('allocore.api_key'));
    }

    /**
     * Build the metrics payload from the audit's per-pillar results.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildMetrics(Audit $audit): array
    {
        $results = $audit->results()->get();

        if ($results->isEmpty()) {
            return [];
        }

        $metrics = [];
        $scores = [];

        foreach ($results as $result) {
            $score = round((float) $result->average_score, 2);
            $scores[] = $score;

            $metrics[] = [
                'key' => $this->keyForPillar($result->level),
                'value' => $score,
                'scale_max' => 5,
            ];
        }

        // Overall Enterprise Readiness = average of the pillar scores.
        array_unshift($metrics, [
            'key' => 'enterprise_readiness',
            'value' => round(array_sum($scores) / max(count($scores), 1), 2),
            'scale_max' => 5,
        ]);

        return $metrics;
    }

    protected function keyForPillar(string $pillarName): string
    {
        return self::PILLAR_KEYS[$pillarName]
            ?? 'audit.'.Str::slug($pillarName, '_');
    }

    protected function ingestUrl(string $hubUrl): string
    {
        return rtrim($hubUrl, '/').'/api/allocore/kpi/ingest';
    }
}
