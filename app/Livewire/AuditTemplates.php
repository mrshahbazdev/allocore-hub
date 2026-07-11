<?php

namespace App\Livewire;

use App\Models\AuditTemplate;
use Livewire\Component;
use Livewire\WithPagination;

class AuditTemplates extends Component
{
    use WithPagination;

    public $search = '';

    public $showModal = false;

    public $isEditing = false;

    public $templateId;

    public $name = '';

    public $description = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['name', 'description', 'templateId']);
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $template = AuditTemplate::findOrFail($id);
        $this->templateId = $template->id;
        $this->name = $template->name;
        $this->description = $template->description;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($this->isEditing) {
            $template = AuditTemplate::findOrFail($this->templateId);
            $template->update([
                'name' => $this->name,
                'description' => $this->description,
            ]);
        } else {
            AuditTemplate::create([
                'name' => $this->name,
                'description' => $this->description,
                'created_by' => auth()->id(),
            ]);
        }

        $this->showModal = false;
        $this->reset(['name', 'description', 'templateId', 'isEditing']);
    }

    public function delete($id)
    {
        $template = AuditTemplate::findOrFail($id);
        $template->delete();
    }

    public function render()
    {
        $templates = AuditTemplate::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            })
            ->withCount('pillars', 'questions', 'audits')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.audit-templates', compact('templates'))
            ->layout('layouts.app');
    }
}
