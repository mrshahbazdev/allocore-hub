<?php

namespace App\Services;

use App\Models\KpiDefinition;
use App\Models\KpiValue;
use App\Models\ToolAccess;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class KpiIngestionService
{
    /**
     * Ingest a batch of metrics pushed by a spoke tool.
     *
     * @param  array<int, array<string, mixed>>  $metrics
     * @return array<string, mixed>
     */
    public function ingest(ToolAccess $access, array $metrics, ?string $externalRef, ?string $recordedAt): array
    {
        $recordDate = $recordedAt ? Carbon::parse($recordedAt)->toDateString() : now()->toDateString();
        $ingested = [];

        DB::transaction(function () use ($access, $metrics, $externalRef, $recordDate, &$ingested) {
            foreach ($metrics as $metric) {
                $key = $metric['key'];
                $value = (float) $metric['value'];
                $scaleMax = isset($metric['scale_max']) ? (float) $metric['scale_max'] : null;

                $definition = $this->resolveDefinition($access, $key, $scaleMax);
                $ref = $this->valueRef($externalRef, $key);

                $kpiValue = KpiValue::updateOrCreate(
                    [
                        'kpi_definition_id' => $definition->id,
                        'external_ref' => $ref,
                    ],
                    [
                        'value' => $value,
                        'recorded_at' => $recordDate,
                        'status' => $this->statusFor($definition, $value),
                        'source' => $access->tool,
                    ]
                );

                $ingested[] = [
                    'key' => $key,
                    'kpi_definition_id' => $definition->id,
                    'value' => $value,
                    'status' => $kpiValue->status,
                ];
            }
        });

        $access->update([
            'status' => 'connected',
            'last_synced_at' => now(),
        ]);

        return $ingested;
    }

    protected function resolveDefinition(ToolAccess $access, string $key, ?float $scaleMax): KpiDefinition
    {
        $meta = ConnectedKpiCatalog::resolve($key, $access->tool);

        return KpiDefinition::firstOrCreate(
            [
                'company_id' => $access->company_id,
                'source' => $access->tool,
                'source_key' => $key,
            ],
            array_merge($meta, [
                'is_connected' => true,
                'is_template' => false,
                'is_active' => true,
                'frequency' => 'monthly',
                'scale_max' => $scaleMax,
            ])
        );
    }

    /**
     * Derive status from thresholds when the entrepreneur has set them,
     * otherwise everything counts as on target.
     */
    protected function statusFor(KpiDefinition $definition, float $value): string
    {
        $critical = $definition->critical_threshold !== null ? (float) $definition->critical_threshold : null;
        $warning = $definition->warning_threshold !== null ? (float) $definition->warning_threshold : null;

        if ($critical === null && $warning === null) {
            return 'on_target';
        }

        $lowerBetter = $definition->direction === 'lower_better';

        if ($lowerBetter) {
            if ($critical !== null && $value >= $critical) {
                return 'critical';
            }
            if ($warning !== null && $value >= $warning) {
                return 'warning';
            }

            return 'on_target';
        }

        if ($critical !== null && $value <= $critical) {
            return 'critical';
        }
        if ($warning !== null && $value <= $warning) {
            return 'warning';
        }

        return 'on_target';
    }

    protected function valueRef(?string $externalRef, string $key): ?string
    {
        if (! $externalRef) {
            return null;
        }

        return $externalRef.'|'.$key;
    }
}
