<?php

namespace App\Livewire;

use App\Models\Audit;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class AuditCompare extends Component
{
    public $audit1Id;

    public $audit2Id;

    public $basePillars = [];

    public function mount()
    {
        $availableAudits = $this->getAvailableAudits();

        if ($availableAudits->count() >= 2) {
            $this->audit1Id = $availableAudits[0]->id;
            $this->audit2Id = $availableAudits[1]->id;
        } elseif ($availableAudits->count() == 1) {
            $this->audit1Id = $availableAudits[0]->id;
        }
    }

    public function getAvailableAudits()
    {
        $userId = auth()->id();
        $companyId = auth()->user()->currentCompany()?->id;

        return Audit::with('company', 'creator', 'template.pillars')
            ->where('status', 'completed')
            ->where(function ($query) use ($userId, $companyId) {
                $query->where('created_by', $userId);
                if ($companyId) {
                    $query->orWhere('company_id', $companyId);
                }
            })
            ->latest()
            ->get();
    }

    public function getAuditData($auditId)
    {
        if (! $auditId) {
            return null;
        }

        $audit = Audit::with(['results', 'company', 'creator', 'template.pillars'])->find($auditId);
        if (! $audit) {
            return null;
        }

        $overallScore = $audit->results->avg('average_score') ?? 0;

        $overallMaturity = 'Beginner';
        if ($overallScore >= 4.5) {
            $overallMaturity = 'Excellent';
        } elseif ($overallScore >= 3.5) {
            $overallMaturity = 'Strong';
        } elseif ($overallScore >= 2.5) {
            $overallMaturity = 'Solid';
        } elseif ($overallScore >= 1.5) {
            $overallMaturity = 'Weak';
        } else {
            $overallMaturity = 'Critical';
        }

        // Prepare data for radar chart (order is important)
        $pillars = $audit->template ? $audit->template->pillars : collect();
        $radarData = [];
        $radarLabels = [];

        if ($pillars->isNotEmpty()) {
            foreach ($pillars as $pillar) {
                $result = $audit->results->where('level', $pillar->name)->first();
                $radarData[] = $result ? (float) $result->average_score : 0;
                $radarLabels[] = $pillar->name;
            }
        } else {
            // Fallback for legacy audits
            $levelsOrder = ['Umsatz', 'Gewinn', 'Ordnung', 'Einfluss', 'Vermächtnis'];
            foreach ($levelsOrder as $level) {
                $result = $audit->results->where('level', $level)->first();
                $radarData[] = $result ? (float) $result->average_score : 0;
                $radarLabels[] = $level;
            }
        }

        return [
            'model' => $audit,
            'overallScore' => $overallScore,
            'overallMaturity' => $overallMaturity,
            'radarData' => $radarData,
            'radarLabels' => $radarLabels,
            'resultsByKey' => $audit->results->keyBy('level'),
            'pillars' => $pillars,
        ];
    }

    public function dispatchChartUpdate()
    {
        $audit1 = $this->getAuditData($this->audit1Id);
        $audit2 = $this->getAuditData($this->audit2Id);

        $this->dispatch(
            'refreshChart',
            audit1Data: $audit1 ? $audit1['radarData'] : null,
            audit2Data: $audit2 ? $audit2['radarData'] : null,
            radarLabels: $audit1 ? $audit1['radarLabels'] : ($audit2 ? $audit2['radarLabels'] : null)
        );
    }

    public function updatedAudit1Id()
    {
        $this->dispatchChartUpdate();
    }

    public function updatedAudit2Id()
    {
        $this->dispatchChartUpdate();
    }

    public function render()
    {
        $audit1 = $this->getAuditData($this->audit1Id);
        $audit2 = $this->getAuditData($this->audit2Id);
        $availableAudits = $this->getAvailableAudits();

        return view('livewire.audit-compare', compact('audit1', 'audit2', 'availableAudits'));
    }
}
