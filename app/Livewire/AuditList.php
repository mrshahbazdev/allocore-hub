<?php

namespace App\Livewire;

use App\Models\Audit;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class AuditList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $audit = Audit::findOrFail($id);
        // Only creator can delete
        if ($audit->created_by !== auth()->id()) {
            session()->flash('error', 'You can only delete your own audits.');

            return;
        }
        $audit->delete();
        session()->flash('success', 'Audit deleted.');
    }

    public function render()
    {
        $companyId = auth()->user()->currentCompany()?->id;

        $audits = Audit::with(['company', 'creator', 'results'])
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->when($this->search, fn ($q) => $q->whereHas(
                'company',
                fn ($cq) => $cq->where('name', 'like', "%{$this->search}%")
            ))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => Audit::where('company_id', $companyId)->count(),
            'completed' => Audit::where('company_id', $companyId)->where('status', 'completed')->count(),
            'in_progress' => Audit::where('company_id', $companyId)->where('status', 'in_progress')->count(),
        ];

        return view('livewire.audit-list', compact('audits', 'stats'));
    }
}
