<?php

namespace App\Livewire;

use App\Models\AuditPillar;
use App\Models\AuditQuestion;
use App\Models\AuditTemplate;
use Livewire\Component;

class AuditTemplateBuilder extends Component
{
    public AuditTemplate $template;

    // Pillar Modal State
    public $showPillarModal = false;

    public $isEditingPillar = false;

    public $activePillarId = null;

    public $pillarName = '';

    public $pillarDescription = '';

    public $pillarIcon = 'account_tree';

    public $pillarTargetScore = 5.0;

    // Question Modal State
    public $showQuestionModal = false;

    public $isEditingQuestion = false;

    public $activeQuestionId = null;

    public $questionText = '';

    public $questionDescription = '';

    public $questionType = 'scale_1_to_5';

    public $selectedPillarIdForQuestion = null;

    // Advanced Question State
    public $questionWeight = 1.0;

    public $questionIsRequired = true;

    public $questionFailureRecommendation = '';

    public $questionOptionsStr = ''; // comma separated string for JSON

    public $questionDependsOnId = '';

    public $questionDependsOnAnswer = '';

    public function mount(AuditTemplate $template)
    {
        $this->template = $template;
    }

    // --- PILLAR MANAGEMENT ---

    public function createPillar()
    {
        $this->resetValidation();
        $this->reset(['pillarName', 'pillarDescription', 'activePillarId', 'pillarIcon']);
        $this->pillarTargetScore = 5.0;
        $this->isEditingPillar = false;
        $this->showPillarModal = true;
    }

    public function editPillar($id)
    {
        $this->resetValidation();
        $pillar = AuditPillar::findOrFail($id);
        $this->activePillarId = $pillar->id;
        $this->pillarName = $pillar->name;
        $this->pillarDescription = $pillar->description;
        $this->pillarIcon = $pillar->icon ?? 'account_tree';
        $this->pillarTargetScore = $pillar->target_score ?? 5.0;
        $this->isEditingPillar = true;
        $this->showPillarModal = true;
    }

    public function savePillar()
    {
        $this->validate([
            'pillarName' => 'required|string|max:255',
            'pillarDescription' => 'nullable|string',
            'pillarIcon' => 'nullable|string',
            'pillarTargetScore' => 'required|numeric|min:0|max:10',
        ]);

        if ($this->isEditingPillar) {
            $pillar = AuditPillar::findOrFail($this->activePillarId);
            $pillar->update([
                'name' => $this->pillarName,
                'description' => $this->pillarDescription,
                'icon' => $this->pillarIcon,
                'target_score' => $this->pillarTargetScore,
            ]);
        } else {
            $order = $this->template->pillars()->max('order') ?? 0;
            $this->template->pillars()->create([
                'name' => $this->pillarName,
                'description' => $this->pillarDescription,
                'icon' => $this->pillarIcon,
                'target_score' => $this->pillarTargetScore,
                'order' => $order + 1,
            ]);
        }

        $this->showPillarModal = false;
    }

    public function deletePillar($id)
    {
        $pillar = AuditPillar::findOrFail($id);
        $pillar->delete();
    }

    // --- QUESTION MANAGEMENT ---

    public function createQuestion($pillarId)
    {
        $this->resetValidation();
        $this->reset([
            'questionText',
            'questionDescription',
            'activeQuestionId',
            'questionFailureRecommendation',
            'questionOptionsStr',
            'questionDependsOnId',
            'questionDependsOnAnswer',
        ]);
        $this->selectedPillarIdForQuestion = $pillarId;
        $this->questionType = 'scale_1_to_5';
        $this->questionWeight = 1.0;
        $this->questionIsRequired = true;
        $this->isEditingQuestion = false;
        $this->showQuestionModal = true;
    }

    public function editQuestion($id)
    {
        $this->resetValidation();
        $question = AuditQuestion::findOrFail($id);
        $this->activeQuestionId = $question->id;
        $this->questionText = $question->question;
        $this->questionDescription = $question->description;
        $this->questionType = $question->question_type ?? 'scale_1_to_5';
        $this->selectedPillarIdForQuestion = $question->pillar_id;

        $this->questionWeight = $question->weight ?? 1.0;
        $this->questionIsRequired = (bool) $question->is_required;
        $this->questionFailureRecommendation = $question->failure_recommendation;
        $this->questionOptionsStr = is_array($question->options) ? implode(',', $question->options) : '';
        $this->questionDependsOnId = $question->depends_on_question_id;
        $this->questionDependsOnAnswer = $question->depends_on_answer;

        $this->isEditingQuestion = true;
        $this->showQuestionModal = true;
    }

    public function saveQuestion()
    {
        $this->validate([
            'questionText' => 'required|string',
            'questionDescription' => 'nullable|string',
            'questionType' => 'required|string',
            'questionWeight' => 'required|numeric|min:0.1|max:10',
            'questionIsRequired' => 'boolean',
            'questionFailureRecommendation' => 'nullable|string',
            'questionOptionsStr' => 'nullable|string',
            'questionDependsOnId' => 'nullable|exists:audit_questions,id',
            'questionDependsOnAnswer' => 'nullable|string',
        ]);

        $optionsArray = null;
        if (in_array($this->questionType, ['select', 'checkbox', 'radio']) && ! empty(trim($this->questionOptionsStr))) {
            $optionsArray = array_map('trim', explode(',', $this->questionOptionsStr));
            $optionsArray = array_filter($optionsArray); // Remove empty options
        }

        $data = [
            'question' => $this->questionText,
            'description' => $this->questionDescription,
            'question_type' => $this->questionType,
            'weight' => $this->questionWeight,
            'is_required' => $this->questionIsRequired,
            'failure_recommendation' => $this->questionFailureRecommendation,
            'options' => $optionsArray,
            'depends_on_question_id' => empty($this->questionDependsOnId) ? null : $this->questionDependsOnId,
            'depends_on_answer' => empty($this->questionDependsOnAnswer) ? null : $this->questionDependsOnAnswer,
        ];

        if ($this->isEditingQuestion) {
            $question = AuditQuestion::findOrFail($this->activeQuestionId);
            $question->update($data);
        } else {
            $data['pillar_id'] = $this->selectedPillarIdForQuestion;
            $this->template->questions()->create($data);
        }

        $this->showQuestionModal = false;
    }

    public function deleteQuestion($id)
    {
        $question = AuditQuestion::findOrFail($id);
        $question->delete();
    }

    public function render()
    {
        $pillars = $this->template->pillars()->with('questions')->orderBy('order')->get();

        return view('livewire.audit-template-builder', compact('pillars'))
            ->layout('layouts.app');
    }
}
